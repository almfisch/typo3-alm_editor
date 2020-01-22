document.addEventListener('DOMContentLoaded', function(){

    CKEDITOR.replace(CKEDITOR.document.getById('alm_editor_field'), {
        language: 'de',
        height: 500,
        width: 'auto',

        stylesSet: [],

        contentsCss: '.cke_editable table { width: 100%; }',

        toolbarGroups: [
            { name: 'clipboard', groups: [ 'undo', 'clipboard' ] },
            { name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
            { name: 'links', groups: [ 'links' ] },
            { name: 'insert', groups: [ 'insert' ] },
            { name: 'forms', groups: [ 'forms' ] },
            { name: 'others', groups: [ 'others' ] },
            { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
            { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
            { name: 'styles', groups: [ 'styles' ] },
            { name: 'tools', groups: [ 'tools' ] },
            { name: 'colors', groups: [ 'colors' ] },
            { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
            { name: 'about', groups: [ 'about' ] }
        ],
    
        removeButtons: 'Underline,PasteFromWord,Scayt,Link,Unlink,Anchor,Image,HorizontalRule,Strike,BulletedList,Indent,Blockquote,Styles,Format,About,NumberedList,Outdent'
    });

});