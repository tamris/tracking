<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;


class ProjectController extends Controller
{
    /**
     * GET /api/projects
     * List semua project milik user
     */
    public function index(Request $request)
    {
        // Ambil project punya user yang sedang login
        $projects = Project::where('user_id', $request->user()->id)
            ->latest('id')
            ->get()
            ->map(function ($project) {
                // Format URL gambar/file biar bisa diakses Flutter
                return $this->formatProjectUrls($project);
            });

        return response()->json([
            'message' => 'List project berhasil diambil',
            'data'    => $projects,
        ]);
    }

    /**
     * POST /api/projects
     * Tambah Project Baru (Upload File support)
     */
    public function store(Request $request)
    {
        // Validasi input
        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'pic'           => ['nullable', 'string', 'max:255'],
            'status'        => ['required', Rule::in(['todo', 'in_progress', 'review', 'done'])],
            'start_date'    => ['nullable', 'date'],
            'end_date'      => ['nullable', 'date', 'after_or_equal:start_date'],
            'progress'      => ['nullable', 'integer', 'min:0', 'max:100'],
            'activity'      => ['nullable', 'string', 'max:255'],
            // File validation
            'contract_file' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx'],
            'cover_image'   => ['nullable', 'image', 'max:4096', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $data['user_id'] = $request->user()->id;
        $data['progress'] = $data['progress'] ?? 0;

        // Handle Upload Contract
        if ($request->hasFile('contract_file')) {
            $data['contract_file'] = $this->storePublicFileKeepName(
                $request->file('contract_file'),
                'contracts'
            );
        }

        // Handle Upload Cover
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->storePublicFileKeepName(
                $request->file('cover_image'),
                'covers'
            );
        }

        $project = Project::create($data);

        return response()->json([
            'message' => 'Project berhasil dibuat',
            'data'    => $this->formatProjectUrls($project),
        ], 201);
    }

    /**
     * GET /api/projects/{id}
     * Detail 1 Project
     */
    public function show(Request $request, $id)
    {
        $project = Project::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$project) {
            return response()->json(['message' => 'Project tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail project',
            'data'    => $this->formatProjectUrls($project),
        ]);
    }

    /**
     * POST /api/projects/{id}?_method=PUT
     * Update Project
     * PENTING: Di Flutter gunakan POST request dengan body field '_method' = 'PUT'
     * jika mengirim file (Multipart), karena Laravel kadang tidak membaca file di method PUT murni.
     */
    public function update(Request $request, $id)
    {
        $project = Project::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$project) {
            return response()->json(['message' => 'Project tidak ditemukan'], 404);
        }

        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'pic'           => ['nullable', 'string', 'max:255'],
            'status'        => ['required', Rule::in(['todo', 'in_progress', 'review', 'done'])],
            'start_date'    => ['nullable', 'date'],
            'end_date'      => ['nullable', 'date', 'after_or_equal:start_date'],
            'progress'      => ['nullable', 'integer', 'min:0', 'max:100'],
            'activity'      => ['nullable', 'string', 'max:255'],
            'contract_file' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx'],
            'cover_image'   => ['nullable', 'image', 'max:4096', 'mimes:jpg,jpeg,png,webp'],
        ]);

        // Handle Contract File Update
        if ($request->hasFile('contract_file')) {
            $this->deletePublicIfExists($project->contract_file); // hapus lama
            $data['contract_file'] = $this->storePublicFileKeepName(
                $request->file('contract_file'),
                'contracts'
            );
        }

        // Handle Cover Image Update
        if ($request->hasFile('cover_image')) {
            $this->deletePublicIfExists($project->cover_image); // hapus lama
            $data['cover_image'] = $this->storePublicFileKeepName(
                $request->file('cover_image'),
                'covers'
            );
        }

        $project->update($data);

        return response()->json([
            'message' => 'Project berhasil diperbarui',
            'data'    => $this->formatProjectUrls($project),
        ]);
    }

    /**
     * DELETE /api/projects/{id}
     * Hapus Project
     */
    public function destroy(Request $request, $id)
    {
        $project = Project::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$project) {
            return response()->json(['message' => 'Project tidak ditemukan'], 404);
        }

        // Hapus file fisik
        $this->deletePublicIfExists($project->contract_file);
        $this->deletePublicIfExists($project->cover_image);

        $project->delete();

        return response()->json([
            'message' => 'Project berhasil dihapus',
        ]);
    }

    /* =========================================================
     | HELPER FUNCTIONS (Sama logicnya dengan Controller Web)
     ========================================================= */

    private function formatProjectUrls($project)
    {
        // Tambahkan atribut URL lengkap agar Flutter tinggal load network image/link
        $project->cover_image_url = $project->cover_image 
            ? url('storage/' . $project->cover_image) 
            : null;
            
        $project->contract_file_url = $project->contract_file 
            ? url('storage/' . $project->contract_file) 
            : null;

        return $project;
    }

    private function deletePublicIfExists(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function storePublicFileKeepName($file, string $folder): string
    {
        $original = $file->getClientOriginalName();
        $name = pathinfo($original, PATHINFO_FILENAME);
        $ext  = $file->getClientOriginalExtension();

        // Bersihkan nama file
        $name = preg_replace('/[^\pL\pN\-\_\s]/u', '', $name);
        $name = preg_replace('/\s+/', ' ', trim($name));
        $name = $name !== '' ? $name : 'file';

        $candidate = $name . '.' . $ext;
        $i = 1;

        // Cek duplikat
        while (Storage::disk('public')->exists($folder.'/'.$candidate)) {
            $candidate = $name." ($i).".$ext;
            $i++;
        }

        return $file->storeAs($folder, $candidate, 'public');
    }
}