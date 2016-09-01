<?php

class MarkdownCloudinaryUpload extends Extension {
    /**
     * update the field holder adding new javascript
     */
    public function updateFieldHolder(){
        if(Config::inst()->get('MarkdownCloudinaryUpload', 'enable') == true){
            Requirements::javascript('markdown/javascript/MarkdownCloudinaryUpload.js');
        }
    }
}

class MarkdownCloudinaryUpload_Controller extends Controller {
    private static $allowed_actions = array(
        'ImageForm',
        'getImageTag'
    );

    public function Image()
    {
        return new CloudinaryImage();
    }

    /**
     * @return Form
     */
    public function ImageForm(){

        Requirements::css(FRAMEWORK_DIR .'/admin/thirdparty/jquery-notice/jquery.notice.css');
        Requirements::css(FRAMEWORK_DIR .'/thirdparty/jquery-ui-themes/smoothness/jquery-ui.css');
        Requirements::css(FRAMEWORK_DIR .'/thirdparty/jstree/themes/apple/style.css');
        Requirements::css(FRAMEWORK_DIR .'/css/GridField.css');
        Requirements::css(FRAMEWORK_DIR .'/admin/css/screen.css');
        Requirements::css(CMS_DIR . '/css/screen.css');

        Requirements::javascript(FRAMEWORK_DIR .'/thirdparty/jquery/jquery.js');
        Requirements::javascript(FRAMEWORK_DIR .'/thirdparty/jquery-entwine/dist/jquery.entwine-dist.js');

        $numericLabelTmpl = '<span class="step-label"><span class="flyout">%d</span><span class="arrow"></span>'
            . '<strong class="title">%s</strong></span>';

        $form = new Form(
            $this,
            "ImageForm",
            new FieldList(
                $contentComposite = new CompositeField(
                    new LiteralField('Step1',
                        '<div class="step1">'
                        . sprintf($numericLabelTmpl, '1', _t('HtmlEditorField.SELECTIMAGE', 'Select Image')) . '</div>'
                    ),
                    CloudinaryImageField::create('Image')->addExtraClass('markdown-popup'),
                    new LiteralField('Step2',
                        '<div class="step2">'
                        . sprintf($numericLabelTmpl, '2', _t('HtmlEditorField.DETAILS', 'Details')) . '</div>'
                    ),
                    NumericField::create('Width'),
                    NumericField::create('Height'),
                    TextField::create('AltText')->setTitle('Alternate Text')
                )
            ),
            new FieldList(
                FormAction::create('insert', _t('HtmlEditorField.BUTTONINSERTIMAGE', 'Insert Image'))
                    ->addExtraClass('ss-ui-action-constructive')
                    ->setAttribute('data-icon', 'accept')
                    ->setUseButtonTag(true)
            )
        );

        $contentComposite->addExtraClass('ss-insert-image content ss-insert-media');
        $form->setFormAction('cloudinary-upload/ImageForm');
        $form->unsetValidator();
        $form->loadDataFrom($this);
        $form->addExtraClass('markdownfield-form markdowneditorfield-imageform ');
        return $form;
    }

    /**
     * get markdown image url
     *
     * @return string
     */
    public function getImageTag(){
        $strRet = '';
        $arrPieces = array('cloudinary_image');

        if(isset($_POST['Image']) && ($image = $_POST['Image'])) {
            $arrPieces[] = "id='".CloudinaryUtils::public_id($image['URL'])."'";

            if(!empty($_POST['Width'])) {
                $arrPieces[] = "width=" . $_POST['Width'];
            }

            if(!empty($_POST['Height'])) {
                $arrPieces[] = "height=" . $_POST['Height'];
            }

            if(!empty($image['Credit'])) {
                $arrPieces[] = "credit='" . $image['Credit']."'";
            }

            if(!empty($image['Caption'])) {
                $arrPieces[] = "caption='" . $image['Caption']."'";
            }

            $arrPieces[] = "gravity='" . $image['Gravity']."'";

            if(!empty($_POST['AltText'])) {
                $arrPieces[] = "alt='".$_POST['AltText']."'";
            }

            $strRet = '['. implode(', ', $arrPieces) . ']';
        }

        return Convert::array2json(array(
            'Markdown'  => $strRet
        ));
    }
}
