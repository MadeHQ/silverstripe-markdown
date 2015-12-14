if (typeof MadeUtils === 'undefined') { var MadeUtils = {};}


(function($) {

    MadeUtils.CloudinaryMarkdown = {

        'CodeMirror'                : null,

        ClearForm: function(dialog){
            dialog.find('ul.ss-uploadfield-files .ss-uploadfield-item.template-download.in').remove();
            dialog.find('#Form_ImageForm_Width').val('');
            dialog.find('#Form_ImageForm_Height').val('');
            dialog.find('#Form_ImageForm_AltText').val('');
            dialog.find('#Form_ImageForm_Caption').val('');
        },

        CloudinaryImagePopup : function(codemirror){
            MadeUtils.CloudinaryMarkdown.CodeMirror = codemirror;

            var url = 'cloudinary-upload/ImageForm/forTemplate',
                dialog = $('.markdowneditorfield-cloudinarydialog');

            if(dialog.length) {
               $(dialog).open();
                MadeUtils.CloudinaryMarkdown.ClearForm(dialog);
            } else {
                dialog = $('<div class="markdowneditorfield-dialog markdowneditorfield-cloudinarydialog loading">');
                $('body').append(dialog);
                $.ajax({
                    url: url,
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

    $('.markdowneditorfield-cloudinarydialog').entwine({
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
            $('#Form_ImageForm_error').removeClass('bad').hide();
            $('.cloudinaryimage.markdown-popup').find('.ss-uploadfield-addfile').show();
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

    $('form.markdowneditorfield-imageform').entwine({

        getDialog: function(){
            return this.closest('.markdowneditorfield-cloudinarydialog');
        },

        onsubmit: function(e) {

            var self = this;
            var data = $(this).serializeArray();
            $(this).addClass('changed').addClass('loading');

            $.ajax({
                url         : 'MarkdownCloudinaryUpload_Controller/getImageTag',
                data        : data,
                dataType    : 'json',
                type        : 'POST',
                success     : function(data){
                    if(data.Markdown) {
                        var cm = MadeUtils.CloudinaryMarkdown.CodeMirror;
                        cm.replaceSelection( data.Markdown );
                        self.getDialog().close();
                    } else {
                        $('#Form_ImageForm_error').addClass('bad').html('Please attach an image before submit.').show();
                    }
                    $(self).removeClass('changed').removeClass('loading');
                }
            });


            return false;
        }
    });

})(jQuery);

