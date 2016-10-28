<script src="{{ asset('assets/tinymce_4.4.3/js/tinymce/tinymce.min.js') }}"></script>
<script>
    window.tinyMCEConfig = {
        selector: '.tinymce',
        plugins : [
            'advlist autolink link image media lists charmap print preview code autosave ' +
            'autoresize charmap anchor textcolor colorpicker contextmenu fullscreen hr nonbreaking ' +
            'paste save searchreplace tabfocus table wordcount'
        ],
        autoresize_max_height: 600,
        media_alt_source: false,
        media_poster: false,
        media_filter_html: true,
        paste_as_text: true,
        toolbar: [
            'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | ' +
            'bullist numlist outdent indent | forecolor backcolor | link anchor image media | ' +
            'fullscreen' // | save
        ],
        image_advtab: true ,
        relative_urls: false,
        external_filemanager_path: "{{ asset('assets/filemanager') }}/",
        filemanager_title: "Ava File Manager",
//        filemanager_sort_by: 'name',
//        filemanager_descending: 0,
        external_plugins: { "filemanager" : "{{ asset('assets/filemanager/plugin.min.js') }}" }
//        setup: function(editor) {
//            editor.on('change keyup', function(e) {
//                $(tinyMCEConfig.selector).val(editor.getContent()).trigger('change');
//            });
//        }

    };
</script>