if (typeof MadeUtils === 'undefined') { var MadeUtils = {};}

(function($) {
    $.entwine('ss', function($) {
        MadeUtils.MarkDownShortCode = {
            CurrentEditor: '',
            CurrentDialog: '',
            OpenDialog: function(cm){
                MadeUtils.MarkDownShortCode.CurrentEditor = cm;
                dialog = $('.markdowneditorfield-shortcodedialog');
                if(dialog.length) {
                    dialog.open();
                } else {
                    dialog = $('<div class="markdowneditorfield-dialog markdowneditorfield-shortcodedialog loading">');
                    MadeUtils.MarkDownShortCode.CurrentDialog = dialog;
                    $('body').append(dialog);
                    $.ajax({
                        url: 'ShortcodableController/ShortcodeForm/forTemplate',
                        complete: function() {
                            dialog.removeClass('loading');
                        },
                        success: function(html) {
                            dialog.html(html);
                            dialog.trigger('ssdialogopen');
                        }
                    });
                }
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
    });
})(jQuery);





