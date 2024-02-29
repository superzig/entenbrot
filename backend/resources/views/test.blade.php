<form action="{{ route('validate.update.x') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="col-lg-12">
        <div class="form-floating mb-4">
            <div class="input-group">
                <input class="form-control"
                       type="file" id="input-file"
                       name="file">
            </div>
            <div id="inputHelp" class="form-text mb-3">Mögliche Dateiformate: .xlsx</div>
        </div>
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-sm btn-alt-info">
                <i class="fa fa-check opacity-50 me-1"></i>
                <span>Hochladen</span>
            </button>
        </div>
    </div>
</form>

