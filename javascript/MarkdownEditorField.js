if (typeof MadeUtils === 'undefined') { var MadeUtils = {};}

(function($) {
    $.entwine('ss', function($) {
        MadeUtils.MarkDownEditor = {

            'CodeMirror'                : null,

            LinkDialog : function(codemirror){
                MadeUtils.MarkDownEditor.CodeMirror = codemirror;

                var self = this, url = $('#cms-editor-dialogs').data('url-linkform'),
                    dialog = $('.markdowneditorfield-linkdialog');


                if(dialog.length) {
                    dialog.open();
                    MadeUtils.MarkDownEditor.SetDefaultValues(dialog);
                } else {
                    dialog = $('<div class="markdowneditorfield-dialog markdowneditorfield-linkdialog loading">');
                    $('body').append(dialog);
                    $.ajax({
                        url: url,
                        complete: function() {
                            dialog.removeClass('loading');
                        },
                        success: function(html) {
                            dialog.html(html);
                            MadeUtils.MarkDownEditor.SetDefaultValues(dialog);
                            dialog.trigger('ssdialogopen');
                        }
                    });
                }

            },

            SetDefaultValues: function(dialog){
                var codeMirror = MadeUtils.MarkDownEditor.CodeMirror;
                var strText = codeMirror.getSelection();
                var regex = /\".*\":.*/;


                dialog.find('.field#LinkText').find('input').val('');
                dialog.find('.field#Description').find('input').val('');
                dialog.find('.treedropdownfield-title').text('(Choose or Search)');
                dialog.find('#Form_EditorToolbarLinkForm_internal').val('');
                dialog.find('#Form_EditorToolbarLinkForm_external').val('');
                dialog.find('#Form_EditorToolbarLinkForm_email').val('');
                dialog.find('#Form_EditorToolbarLinkForm_Anchor').val('');
                dialog.find('#Form_EditorToolbarLinkForm_TargetBlank').removeAttr('checked');

                if(strText){
                    if(regex.test(strText)){
                        dialog.find(':input[name=LinkType]').removeAttr('checked');
                        var parts = strText.split("\":");
                        var linkText = parts[0].substr(1);
                        var url = parts[1];
                        var linkTitle = '';
                        var className = '';

                        if(linkText.indexOf('(') == 0){
                            var text = linkText;
                            className = text.substr(1, text.indexOf(')') - 1);
                            linkText = text.substr(text.indexOf(')') + 1);
                        }

                        if(linkText.indexOf('(') != -1 && linkText.indexOf(')') != -1){
                            var defaultText = linkText;
                            linkText = linkText.substr(0, linkText.indexOf('('));
                            linkTitle = defaultText.substr(defaultText.indexOf('(') + 1).replace(')', '');
                        }

                        if(url.indexOf('sitetree_link,id=') != -1){
                            dialog.find('#Form_EditorToolbarLinkForm_LinkType_internal').attr('checked', 'checked');
                            dialog.find('#Form_EditorToolbarLinkForm_internal').val(url.replace('[sitetree_link,id=', '').replace(']', ''));
                        }
                        else if (url.indexOf('http') == 0){
                            dialog.find('#Form_EditorToolbarLinkForm_LinkType_external').attr('checked', 'checked');
                            dialog.find('#Form_EditorToolbarLinkForm_external').val(url);
                        }
                        else if (url.indexOf('mailto:') == 0){
                            dialog.find('#Form_EditorToolbarLinkForm_LinkType_email').attr('checked', 'checked');
                            dialog.find('#Form_EditorToolbarLinkForm_email').val(url.replace('mailto:', ''));
                        }
                        else if (url.indexOf('#') == 0){
                            dialog.find('#Form_EditorToolbarLinkForm_LinkType_anchor').attr('checked', 'checked');
                            dialog.find('#Form_EditorToolbarLinkForm_Anchor').val(url.replace('#', ''));
                        }

                        dialog.find('.field#LinkText').find('input').val(linkText);
                        dialog.find('.field#Description').find('input').val(linkTitle);

                        if(className.indexOf('targetblank') != -1){
                            dialog.find('#Form_EditorToolbarLinkForm_TargetBlank').attr('checked', 'checked');
                        }

                    }
                    else{
                        dialog.find('.field#LinkText').find('input').val(strText);
                    }
                }

                $('#Form_EditorToolbarLinkForm').redraw();

            },

            /**
             * Fetch relevant anchors, depending on the link type.
             *
             * @return $.Deferred A promise of an anchor array, or an error message.
             */
            getAnchors: function() {
                var linkType = this.find(':input[name=LinkType]:checked').val();
                var dfdAnchors = $.Deferred();

                switch (linkType) {
                    case 'anchor':
                        // Fetch from the local editor.
                        var collectedAnchors = [];
                        var ed = this.getEditor();
                        // name attribute is defined as CDATA, should accept all characters and entities
                        // http://www.w3.org/TR/1999/REC-html401-19991224/struct/links.html#h-12.2

                        if(ed) {
                            var raw = ed.getContent().match(/name="([^"]+?)"|name='([^']+?)'/gim);
                            if (raw && raw.length) {
                                for(var i = 0; i < raw.length; i++) {
                                    collectedAnchors.push(raw[i].substr(6).replace(/"$/, ''));
                                }
                            }
                        }

                        dfdAnchors.resolve(collectedAnchors);
                        break;

                    case 'internal':
                        // Fetch available anchors from the target internal page.
                        var pageId = this.find(':input[name=internal]').val();

                        if (pageId) {
                            $.ajax({
                                url: $.path.addSearchParams(
                                    this.attr('action').replace('LinkForm', 'getanchors'),
                                    {'PageID': parseInt(pageId)}
                                ),
                                success: function(body, status, xhr) {
                                    dfdAnchors.resolve($.parseJSON(body));
                                },
                                error: function(xhr, status) {
                                    dfdAnchors.reject(xhr.responseText);
                                }
                            });
                        } else {
                            dfdAnchors.resolve([]);
                        }
                        break;

                    default:
                        // This type does not support anchors at all.
                        dfdAnchors.reject(ss.i18n._t(
                            'HtmlEditorField.ANCHORSNOTSUPPORTED',
                            'Anchors are not supported for this link type.'
                        ));
                        break;
                }

                return dfdAnchors.promise();
            },

            getEditorState: function(cm, pos) {
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
            },

            _toggleLine: function(cm, name) {
                if (/editor-preview-active/.test(cm.getWrapperElement().lastChild.className))
                    return;

                var stat = MadeUtils.MarkDownEditor.getEditorState(cm);
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
            }

        };

        $('.markdowneditorfield-dialog').entwine({
            onadd: function() {
                // Create jQuery dialog
                if (!this.is('.ui-dialog-content')) {
                    this.ssdialog({autoOpen: true});
                }

                this._super();
            },

            getForm: function() {
                return this.find('form');
            },
            open: function() {
                this.ssdialog('open');
            },
            close: function() {
                this.ssdialog('close');
            },
            toggle: function(bool) {
                if(this.is(':visible')) this.close();
                else this.open();
            }
        });


        /**
         *
         */

        $('form.markdowneditorfield-linkform input[name=LinkType]').entwine({
            onclick: function(e) {
                this.parents('form:first').redraw();
                this._super();
            },
            onchange: function() {
                this.parents('form:first').redraw();

                // Update if a anchor-supporting link type is selected.
                var linkType = this.parent().find(':checked').val();
                if (linkType==='anchor' || linkType==='internal') {
                    this.parents('form.markdowneditorfield-linkform').updateAnchorSelector();
                }
                this._super();
            }
        });

        $('form.markdowneditorfield-linkform input[name=internal]').entwine({
            /**
             * Update the anchor dropdown if a different page is selected in the "internal" dropdown.
             */
            onvalueupdated: function() {
                this.parents('form.markdowneditorfield-linkform').updateAnchorSelector();
                this._super();
            }
        });

        $('form.markdowneditorfield-linkform :submit[name=action_remove]').entwine({
            onclick: function(e) {
                this.parents('form:first').removeLink();
                this._super();
                return false;
            }
        });

        $('form.markdowneditorfield-linkform').entwine({

            onadd: function(){
                this.redraw()
            },

            getDialog: function(){
                return this.closest('.markdowneditorfield-dialog');
            },

            onsubmit: function(e) {
                var attrs = this.getLinkAttributes();
                var cm = MadeUtils.MarkDownEditor.CodeMirror;
                // (link_text)(link_address "link_title")

                if(attrs.href){

                    var strText = '';

                    if(attrs.target && attrs.target == '_blank') {
                        strText += '<a href="'+attrs.href+'" target="'+attrs.target+'" title="'+attrs.title+'">';
                        strText += (attrs.text ? attrs.text : 'Your text to link here...');
                        strText += '</a>';
                    } else {
                        strText += '['+(attrs.text ? attrs.text : 'Your text to link here...')+']';
                        strText += '(' + attrs.href;

                        if(attrs.title)
                            strText += ' "' + attrs.title + '"';
                        strText += ')'
                    }
                    cm.replaceSelection( strText );
                    this.getDialog().close();
                }

                return false;
            },

            resetFields: function() {
                this._super();

                // Reset the form using a native call. This will also correctly reset checkboxes and radio buttons.
                this[0].reset();
            },

            redraw: function() {
                this._super();

                var linkType = this.find(':input[name=LinkType]:checked').val();

                this.addAnchorSelector();

                // Toggle field visibility depending on the link type.
                this.find('div.content .field').hide();
                this.find('.field#LinkType').show();
                this.find('.field#Form_EditorToolbarLinkForm_' + linkType + '_Holder').show();
                if(linkType == 'internal' || linkType == 'anchor') this.find('.field#Form_EditorToolbarLinkForm_Anchor_Holder').show();
                if(linkType !== 'email') this.find('.field#TargetBlank').show();
                if(linkType == 'anchor') this.find('.field#Form_EditorToolbarLinkForm_Anchor_Holder').show();
                this.find('.field#Form_EditorToolbarLinkForm_Description_Holder').show();
                this.find('.field#Form_EditorToolbarLinkForm_LinkText_Holder').show();


            },

            /**
             * @return Object Keys: 'href', 'target', 'title'
             */
            getLinkAttributes: function() {
                var href, target = null, anchor = this.find(':input[name=Anchor]').val();

                // Determine target
                if(this.find(':input[name=TargetBlank]').is(':checked')) target = '_blank';

                // All other attributes
                switch(this.find(':input[name=LinkType]:checked').val()) {
                    case 'internal':
                        href = '[sitetree_link,id=' + this.find(':input[name=internal]').val() + ']';
                        if(anchor) href += '#' + anchor;
                        break;

                    case 'anchor':
                        href = '#' + anchor;
                        break;

                    case 'email':
                        href = 'mailto:' + this.find(':input[name=email]').val();
                        target = null;
                        break;

                    // case 'external':
                    default:
                        href = this.find(':input[name=external]').val();
                        // Prefix the URL with "http://" if no prefix is found
                        if(href.indexOf('://') == -1) href = 'http://' + href;
                        break;
                }

                return {
                    href : href,
                    target : target,
                    title : this.find(':input[name=Description]').val(),
                    text : this.find(':input[name=LinkText]').val()
                };
            },


            /**
             * Builds an anchor selector element and injects it into the DOM next to the anchor field.
             */
            addAnchorSelector: function() {
                // Avoid adding twice
                if(this.find(':input[name=AnchorSelector]').length) return;

                var self = this;
                var anchorSelector = $(
                    '<select id="Form_EditorToolbarLinkForm_AnchorSelector" name="AnchorSelector"></select>'
                );
                this.find(':input[name=Anchor]').parent().append(anchorSelector);

                // Initialise the anchor dropdown.
                this.updateAnchorSelector();

                // copy the value from dropdown to the text field
                anchorSelector.change(function(e) {
                    self.find(':input[name="Anchor"]').val($(this).val());
                });
            },

            /**
             * Fetch relevant anchors, depending on the link type.
             *
             * @return $.Deferred A promise of an anchor array, or an error message.
             */
            getAnchors: function() {
                var linkType = this.find(':input[name=LinkType]:checked').val();
                var dfdAnchors = $.Deferred();

                switch (linkType) {
                    case 'anchor':
                        // Fetch from the local editor.
                        var collectedAnchors = [];
                        var ed = this.getEditor();
                        // name attribute is defined as CDATA, should accept all characters and entities
                        // http://www.w3.org/TR/1999/REC-html401-19991224/struct/links.html#h-12.2

                        if(ed) {
                            var raw = ed.getContent().match(/name="([^"]+?)"|name='([^']+?)'/gim);
                            if (raw && raw.length) {
                                for(var i = 0; i < raw.length; i++) {
                                    collectedAnchors.push(raw[i].substr(6).replace(/"$/, ''));
                                }
                            }
                        }

                        dfdAnchors.resolve(collectedAnchors);
                        break;

                    case 'internal':
                        // Fetch available anchors from the target internal page.
                        var pageId = this.find(':input[name=internal]').val();

                        if (pageId) {
                            $.ajax({
                                url: $.path.addSearchParams(
                                    this.attr('action').replace('LinkForm', 'getanchors'),
                                    {'PageID': parseInt(pageId)}
                                ),
                                success: function(body, status, xhr) {
                                    dfdAnchors.resolve($.parseJSON(body));
                                },
                                error: function(xhr, status) {
                                    dfdAnchors.reject(xhr.responseText);
                                }
                            });
                        } else {
                            dfdAnchors.resolve([]);
                        }
                        break;

                    default:
                        // This type does not support anchors at all.
                        dfdAnchors.reject(ss.i18n._t(
                            'HtmlEditorField.ANCHORSNOTSUPPORTED',
                            'Anchors are not supported for this link type.'
                        ));
                        break;
                }

                return dfdAnchors.promise();
            },

            /**
             * Update the anchor list in the dropdown.
             */
            updateAnchorSelector: function() {
                var self = this;
                var selector = this.find(':input[name=AnchorSelector]');
                var dfdAnchors = this.getAnchors();

                // Inform the user we are loading.
                selector.empty();
                selector.append($(
                    '<option value="" selected="1">' +
                        ss.i18n._t('HtmlEditorField.LOOKINGFORANCHORS', 'Looking for anchors...') +
                        '</option>'
                ));

                dfdAnchors.done(function(anchors) {
                    selector.empty();
                    selector.append($(
                        '<option value="" selected="1">' +
                            ss.i18n._t('HtmlEditorField.SelectAnchor') +
                            '</option>'
                    ));

                    if (anchors) {
                        for (var j = 0; j < anchors.length; j++) {
                            selector.append($('<option value="'+anchors[j]+'">'+anchors[j]+'</option>'));
                        }
                    }

                }).fail(function(message) {
                    selector.empty();
                    selector.append($(
                        '<option value="" selected="1">' +
                            message +
                            '</option>'
                    ));
                });

                // Poke the selector for IE8, otherwise the changes won't be noticed.
                if ($.browser.msie) selector.hide().show();
            }
        });


        $('textarea.markdowneditor').entwine({
            onmatch: function() {
                var editorTextArea = $(this);

                // Don't attempt to enable fields which have already been enabled
                if( editorTextArea.hasClass('MarkdownEditorEnabled') ) {
                    return;
                } else {
                    editorTextArea.addClass('MarkdownEditorEnabled')
                }

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

                editorTextArea.parent().find('.CodeMirror').css({
                    'height': editorTextArea.height(),
                    'min-height': editorTextArea.height()
                });
            }
        });

    });
})(jQuery);


function drawMarkdownH1(editor){
    var cm = editor.codemirror;
    MadeUtils.MarkDownEditor._toggleLine(cm, 'header-1');
}

function drawMarkdownH2(editor){
    var cm = editor.codemirror;
    MadeUtils.MarkDownEditor._toggleLine(cm, 'header-2');
}

function drawMarkdownH3(editor){
    var cm = editor.codemirror;
    MadeUtils.MarkDownEditor._toggleLine(cm, 'header-3');
}

function drawMarkdownH4(editor){
    var cm = editor.codemirror;
    MadeUtils.MarkDownEditor._toggleLine(cm, 'header-4');
}

function drawMarkdownH5(editor){
    var cm = editor.codemirror;
    MadeUtils.MarkDownEditor._toggleLine(cm, 'header-5');
}

function drawMarkdownH6(editor){
    var cm = editor.codemirror;
    MadeUtils.MarkDownEditor._toggleLine(cm, 'header-6');
}

function drawCMSLink(editor){
    var cm = editor.codemirror;
    MadeUtils.MarkDownEditor.LinkDialog(cm);
}

function drawCloudinaryImage(editor){
    var cm = editor.codemirror;
    MadeUtils.CloudinaryMarkdown.CloudinaryImagePopup(cm);
}

function drawShortCode(editor) {
    var cm = editor.codemirror;
    MadeUtils.MarkDownShortCode.OpenDialog(cm);
}
