<?php

namespace MadeHQ\Markdown\Extensions;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;
use SilverStripe\View\SSViewer;

use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FormAction;

use MadeHQ\Cloudinary\Forms\Image As ImageField;
use MadeHQ\Cloudinary\Models\Image;
use MadeHQ\Cloudinary\Utils;

class MarkdownCloudinaryUpload extends Extension
{
    /**
     * update the field holder adding new javascript
     */
    public function updateFieldHolder()
    {
        if (Config::inst()->get('MarkdownCloudinaryUpload', 'enable') == true) {
            Requirements::javascript(
                MARKDOWN_MODULE_BASE . ':client/js/MarkdownCloudinaryUpload.js'
            );
        }
    }
}

class MarkdownCloudinaryUpload_Controller extends Controller
{
    private static $allowed_actions = array(
        'ImageForm',
        'getImageTag'
    );

    /**
     * Assign themes to use for cms
     *
     * @config
     * @var    array
     */
    private static $admin_themes = [
        'silverstripe/framework:/admin/themes/cms-forms',
        SSViewer::DEFAULT_THEME,
    ];

    protected function init()
    {
        parent::init();
        SSViewer::set_themes($this->config()->admin_themes);
    }

    public function Image()
    {
        return new Image();
    }

    /**
     * @return Form
     */
    public function ImageForm()
    {
        $numericLabelTmpl = '<span class="step-label"><span class="flyout">Step %d.</span>'
        . '<span class="title">%s</span></span>';
        $fields = new FieldList();
        $headerWrapper = CompositeField::create(
            LiteralField::create('Heading', '<h3 class="">Insert Image</h3>')
        );
        $contentComposite = new CompositeField(
            LiteralField::create(
                'Step1',
                '<div class="step1">'
                . sprintf($numericLabelTmpl, '1', _t('HtmlEditorField.SELECTIMAGE', 'Select Image')) . '</div>'
            ),
            ImageField::create('Image')->addExtraClass('markdown-popup'),
            LiteralField::create(
                'Step2',
                '<div class="step2">'
                . sprintf($numericLabelTmpl, '2', _t('HtmlEditorField.DETAILS', 'Details')) . '</div>'
            ),
            TextField::create('Width'),
            TextField::create('Height'),
            TextField::create('AltText')->setTitle('Alternate Text')
        );
        $actions = new FieldList(
            FormAction::create('insert', _t('HtmlEditorField.BUTTONINSERTIMAGE', 'Insert Image'))
                ->addExtraClass('ss-ui-action-constructive')
                ->setAttribute('data-icon', 'accept')
                ->setUseButtonTag(true)
        );
        $fields->push($headerWrapper);
        $fields->push($contentComposite);
        $form = new Form(
            $this,
            "ImageForm",
            $fields,
            $actions
        );

        $headerWrapper->setName('HeaderWrap');
        $headerWrapper->addExtraClass('CompositeField composite cms-content-header form-group--no-label');
        $contentComposite->setName('ContentBody');
        $contentComposite->addExtraClass('ss-insert-image content ss-insert-media');
        $form->setFormAction('cloudinary-upload/ImageForm');
        $form->unsetValidator();
        $form->loadDataFrom($this);
        $form->addExtraClass('markdownfield-form markdowneditorfield-imageform');
        return $form;
    }

    /**
     * get markdown image url
     *
     * @return string
     */
    public function getImageTag()
    {
        $strRet = '';
        $arrPieces = array('cloudinary_image');

        if(isset($_POST['Image']) && ($image = $_POST['Image'])) {
            $arrPieces[] = "id='" . Utils::public_id($image['URL'])."'";

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

        return json_encode(['Markdown'  => $strRet]);
    }
}
