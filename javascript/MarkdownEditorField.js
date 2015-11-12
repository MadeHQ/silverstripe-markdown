function getState(cm, pos) {
    pos = pos || cm.getCursor('start');
    var stat = cm.getTokenAt(pos);
    if (!stat.type) return {};

    var types = stat.type.split(' ');
    var ret = {},
        data, text;
    for (var i = 0; i < types.length; i++) {
        data = types[i];
        if (data === 'strong') {
            ret.bold = true;
        } else if (data === 'variable-2') {
            text = cm.getLine(pos.line);
            if (/^\s*\d+\.\s/.test(text)) {
                ret['ordered-list'] = true;
            } else {
                ret['unordered-list'] = true;
            }
        } else if (data === 'atom') {
            ret.quote = true;
        } else if (data === 'em') {
            ret.italic = true;
        } else if (data === 'quote') {
            ret.quote = true;
        }
        else if (data === 'header-1') {
            ret['header-1'] = true;
        }
        else if (data === 'header-2') {
            ret['header-2'] = true;
        }
        else if (data === 'header-3') {
            ret['header-3'] = true;
        }
        else if (data === 'header-4') {
            ret['header-4'] = true;
        }
        else if (data === 'header-5') {
            ret['header-5'] = true;
        }
        else if (data === 'header-6') {
            ret['header-6'] = true;
        }
    }

    return ret;
};

function _toggleLine(cm, name) {
    if (/editor-preview-active/.test(cm.getWrapperElement().lastChild.className))
        return;

    var stat = getState(cm);
    var startPoint = cm.getCursor('start');
    var endPoint = cm.getCursor('end');
    var repl = {
        'quote': /^(\s*)\>\s+/,
        'unordered-list': /^(\s*)(\*|\-|\+)\s+/,
        'ordered-list': /^(\s*)\d+\.\s+/,

        'header-1': /^(\s*)\#\s+/,
        'header-2': /^(\s*)\#\s+/,
        'header-3': /^(\s*)\#\s+/,
        'header-4': /^(\s*)\#\s+/,
        'header-5': /^(\s*)\#\s+/,
        'header-6': /^(\s*)\#\s+/
    };
    var map = {
        'quote': '> ',
        'unordered-list': '* ',
        'ordered-list': '1. ',
        'header-1': '# ',
        'header-2': '## ',
        'header-3': '### ',
        'header-4': '#### ',
        'header-5': '##### ',
        'header-6': '###### '
    };
    for (var i = startPoint.line; i <= endPoint.line; i++) {
        (function(i) {
            var text = cm.getLine(i);
            if (stat[name]) {
                text = text.replace(repl[name], '$1');
            } else {
                text = map[name] + text;
            }
            cm.replaceRange(text, {
                line: i,
                ch: 0
            }, {
                line: i,
                ch: 99999999999999
            });
        })(i);
    }
    cm.focus();
};


function drawMarkdownH1(editor){
    var cm = editor.codemirror;
    _toggleLine(cm, 'header-1');
};

function drawMarkdownH2(editor){
    var cm = editor.codemirror;
    _toggleLine(cm, 'header-2');
};

function drawMarkdownH3(editor){
    var cm = editor.codemirror;
    _toggleLine(cm, 'header-3');
};

function drawMarkdownH4(editor){
    var cm = editor.codemirror;
    _toggleLine(cm, 'header-4');
};

function drawMarkdownH5(editor){
    var cm = editor.codemirror;
    _toggleLine(cm, 'header-5');
};

function drawMarkdownH6(editor){
    var cm = editor.codemirror;
    _toggleLine(cm, 'header-6');
};


/**
 * shortcode register
 */

SimpleMDE.shortCode = shortCode;

/**
 * Action for adding shortcode
 */
function shortCode(editor) {
    var cm = editor.codemirror;
    MadeUtils.MarkDownEditor.OpenDialog(cm);
}


SimpleMDE.prototype.shortCode = function() {
    shortCode(this);
};



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
                    var form = editorTextArea.closest('.cms-edit-form');
                    form.find('#Form_EditForm_action_save').button({showingAlternate: true});
                    form.find('#Form_EditForm_action_publish').button({showingAlternate: true});
                    editorTextArea.val(simplemde.value());
                });
            }

        });
    });
})(jQuery);