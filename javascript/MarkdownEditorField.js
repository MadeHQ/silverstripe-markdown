(function($) {
    $.entwine('ss', function($) {
        $('textarea.markdowneditor').entwine({

            onmatch : function(){

                var editorTextArea = $(this);

                var configs = {};
                var configsKey = editorTextArea.attr('configs');
                if(typeof markdownEditorConfigs !== 'undefined' && typeof markdownEditorConfigs[configsKey] !== 'undefined'){
                    var configTemplate = markdownEditorConfigs[configsKey];
                    $.extend(configs, configTemplate);
                }

                if(configs.toolbar){
                    for(key in configs.toolbar){
                        var button = configs.toolbar[key];

                        if(typeof window[button.action] == 'function'){
                            button.action = window[button.action];
                        }

                    }
                }

                configs.element = editorTextArea[0];

                var simplemde = new SimpleMDE(configs);
                simplemde.render();

                simplemde.codemirror.on("change", function(){
                    editorTextArea.val(simplemde.value());
                });
            }

        });
    });
})(jQuery);