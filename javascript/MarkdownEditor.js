(function($) {
    $.entwine('ss', function($) {
        $('textarea.markdowneditor').entwine({
            TextArea: null,
            Div: null,
            Editor: null,
            Frame: null,            
            SoftTabs: true,
            onmatch: function() {
                $(this).setFrame({
                    width: '100%',
                    height: $(this).height()
                });
                
                $(this).setTextArea($(this));
                $(this).hide();
                
                var div = $('<div id="'+$(this).attr('ID')+'_Editor" class="markdowneditor_editor"/>').css('height', $(this).getFrame().height).css('width', $(this).getFrame().width).text($(this).val());
                div.insertAfter($(this));
                $(this).setDiv(div);                
                
                var editor = ace.edit(div.get(0));
                editor.getSession().setMode('ace/mode/markdown');
                editor.setTheme('ace/theme/github');
                editor.resize();
                editor.$blockScrolling = Infinity;
    
                // configure
                editor.getSession().setUseWrapMode(true);
                editor.getSession().setWrapLimitRange(80, 80);

                editor.setShowPrintMargin(true);
                editor.renderer.setShowGutter(false);

                $(this).setEditor(editor);
                
                var code = $(this).val();

                $(this).setUseSoftTabs($(this).usesSoftTabs(code));
                $(this).setTabSize($(this).getSoftTabs() ? $(this).guessTabSize(code):8);
                $(this).setupFormBindings();

                $(this).setupKeyBindings();
            },
            code: function() {
                return $(this).getEditor().getSession().getValue();
            },
            setupFormBindings: function() {
                var self=$(this);
                $(this).getEditor().getSession().on("change", function() {
                    self.getTextArea().val(self.code()).change();
                });
            },
            setUseSoftTabs: function(val) {
                $(this).setSoftTabs(val);
                $(this).getEditor().getSession().setUseSoftTabs(val);
            },
            setTabSize: function(val) {
                $(this).getEditor().getSession().setTabSize(val);
            },
            setUseWrapMode: function(val) {
                $(this).getEditor().getSession().setUseWrapMode(val);
            },
            guessTabSize: function(val) {
                var result=/^( +)[^*]/im.exec(val || $(this).code());
                return (result ? result[1].length:2);
            },
            usesSoftTabs: function(val) {
                return !/^\t/m.test(val || $(this).code());
            },
            setupKeyBindings: function() {
                $(this).getEditor().commands.addCommand({
                    name: "bold",
                    bindKey: {
                        win: "Ctrl-B",
                        mac: "Command-B"
                    },
                    exec: function(editor) {
                        if (editor.selection.isEmpty()) {
                            var content = "****";
                            
                            editor.insert(content);
                            editor.selection.moveCursorBy(0, -2);

                            var cursor = editor.selection.getCursor();
                            editor.selection.selectTo(cursor.row, cursor.column);
                        } else {
                            var content = editor.session.getTextRange(editor.getSelectionRange());

                            if ("**" == content.substring(0, 2) && "**" == content.substring(content.length - 2)) {
                                content = content.replace(/\*/g, "");
                                editor.insert(content);
                            } else {
                                content = "**" + content + "**";
                                editor.insert(content);
                            }

                            var cursor = editor.selection.getCursor();
                            editor.selection.moveCursorBy(0, 0 - content.length);
                            cursor = editor.selection.getCursor();
                            editor.selection.selectTo(cursor.row, cursor.column + content.length);
                        }
                    }
                });
            }
        });
    });
})(jQuery);