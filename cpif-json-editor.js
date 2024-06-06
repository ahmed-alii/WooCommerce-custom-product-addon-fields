jQuery(document).ready(function($) {
    var editor = ace.edit("json-editor");
    editor.setTheme("ace/theme/github");
    editor.session.setMode("ace/mode/json");
    editor.session.setValue($('#cpif_json_config').val());

    $('form').on('submit', function() {
        $('#cpif_json_config').val(editor.getValue());
    });
});
