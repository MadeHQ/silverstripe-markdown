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

    /**
     * @return Form
     */
    public function ImageForm(){

		Requirements::css('framework/admin/thirdparty/jquery-notice/jquery.notice.css');
		Requirements::css('framework/thirdparty/jquery-ui-themes/smoothness/jquery-ui.css');
		Requirements::css('framework/admin/thirdparty/chosen/chosen/chosen.css');
		Requirements::css('framework/thirdparty/jstree/themes/apple/style.css');
		Requirements::css('framework/css/TreeDropdownField.css');
		Requirements::css('framework/css/GridField.css');
		Requirements::css('framework/admin/css/screen.css');
		Requirements::css('site/css/cms.css');
		Requirements::css('dashboard/css/dashboard_icon.css');
		Requirements::css('userforms/css/FieldEditor.css');
		Requirements::css('cms/css/screen.css');

		Requirements::javascript('framework/thirdparty/jquery/jquery.js');
		Requirements::javascript('framework/thirdparty/jquery-ui/jquery-ui.js');
		Requirements::javascript('framework/thirdparty/jquery-entwine/dist/jquery.entwine-dist.js');
		Requirements::javascript('framework/thirdparty/jquery-cookie/jquery.cookie.js');
		Requirements::javascript('framework/javascript/GridField.js');
		Requirements::javascript('framework/admin/javascript/leaktools.js');
		Requirements::javascript('framework/admin/javascript/LeftAndMain.Layout.js');
		Requirements::javascript('framework/admin/javascript/LeftAndMain.js');
		Requirements::javascript('framework/admin/javascript/LeftAndMain.ActionTabSet.js');
		Requirements::javascript('framework/admin/javascript/LeftAndMain.Panel.js');
		Requirements::javascript('framework/admin/javascript/LeftAndMain.Tree.js');
		Requirements::javascript('framework/admin/javascript/LeftAndMain.Content.js');
		Requirements::javascript('framework/admin/javascript/LeftAndMain.EditForm.js');
		Requirements::javascript('framework/admin/javascript/LeftAndMain.Menu.js');
		Requirements::javascript('framework/admin/javascript/LeftAndMain.Preview.js');
		Requirements::javascript('framework/admin/javascript/LeftAndMain.BatchActions.js');
		Requirements::javascript('framework/admin/javascript/LeftAndMain.FieldHelp.js');
		Requirements::javascript('framework/admin/javascript/LeftAndMain.TreeDropdownField.js');
		Requirements::javascript('framework/javascript/lang/en.js');

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
                    TextField::create('AltText')->setTitle('Alter Text')
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
		if(isset($_POST['Image']) && $_POST['Image']
			&& isset($_POST['Width'])
			&& isset($_POST['Height'])
			&& isset($_POST['AltText'])
		){
			$arrImages = reset($_POST['Image']);
			$strRet = "[cloudinary_image,id=".$arrImages[0];
			if(!empty($_POST['Width']) && !empty($_POST['Height']))
				$strRet .= ",width=" . $_POST['Width'] . ",height=" . $_POST['Height'];

		    if(!empty($_POST['AltText']))
				$strRet .= ",alt=".$_POST['AltText'];

			$strRet .= "]";
		}
		return Convert::array2json(array(
			'Markdown'	=> $strRet
		));
	}
}