<div id="modalProject" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
    
    <div style="background:#fff; padding:20px; border-radius:10px; width:500px; max-width:90%;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h2 class="modal-title" style="margin:0;">Tambah Project</h2>
            <button type="button" onclick="closeModal()" style="border:none; background:none; font-size:20px; cursor:pointer;">&times;</button>
        </div>

        <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:10px;">
                <label style="display:block;">Nama Project</label>
                <input type="text" name="title" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;" required>
            </div>
            
            <div style="display:flex; gap:10px; margin-bottom:10px;">
                <div style="flex:1;">
                    <label>PIC</label>
                    <input type="text" name="pic" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
                </div>
                <div style="flex:1;">
                    <label>Status</label>
                    <select name="status" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
                        <option value="todo">Belum Mulai</option>
                        <option value="in_progress">In Progress</option>
                        <option value="review">Review</option>
                        <option value="done">Selesai</option>
                    </select>
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-bottom:10px;">
                <div style="flex:1;">
                    <label>Mulai</label>
                    <input type="date" name="start_date" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
                </div>
                <div style="flex:1;">
                    <label>Selesai</label>
                    <input type="date" name="end_date" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
                </div>
            </div>

            <!-- <div style="margin-bottom:10px;">
                <label>Progress (%)</label>
                <input type="number" name="progress" min="0" max="100" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;">
            </div> -->

            <div style="margin-bottom:15px;">
                <label>File Kontrak</label>
                <input type="file" name="contract_file" style="width:100%;">
            </div>

            <div style="text-align:right;">
                <button type="submit" class="btn btn-brand" style="padding:10px 20px;">Simpan Project</button>
            </div>
        </form>
    </div>
</div>