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

		Requirements::css(FRAMEWORK_DIR .'/admin/thirdparty/jquery-notice/jquery.notice.css');
		Requirements::css(FRAMEWORK_DIR .'/thirdparty/jquery-ui-themes/smoothness/jquery-ui.css');
		Requirements::css(FRAMEWORK_DIR .'/admin/thirdparty/chosen/chosen/chosen.css');
		Requirements::css(FRAMEWORK_DIR .'/thirdparty/jstree/themes/apple/style.css');
		Requirements::css(FRAMEWORK_DIR .'/css/TreeDropdownField.css');
		Requirements::css(FRAMEWORK_DIR .'/css/GridField.css');
		Requirements::css(FRAMEWORK_DIR .'/admin/css/screen.css');
		Requirements::css(CMS_DIR . '/css/screen.css');

		Requirements::javascript(FRAMEWORK_DIR . '/thirdparty/jquery/jquery.js');
		Requirements::javascript(FRAMEWORK_DIR .'/thirdparty/jquery-ui/jquery-ui.js');
		Requirements::javascript(FRAMEWORK_DIR .'/thirdparty/jquery-entwine/dist/jquery.entwine-dist.js');
		Requirements::javascript(FRAMEWORK_DIR .'/thirdparty/jquery-cookie/jquery.cookie.js');
		Requirements::javascript(FRAMEWORK_DIR .'/javascript/GridField.js');
		Requirements::javascript(FRAMEWORK_DIR .'/admin/javascript/leaktools.js');
		Requirements::javascript(FRAMEWORK_DIR .'/admin/javascript/LeftAndMain.Layout.js');
		Requirements::javascript(FRAMEWORK_DIR .'/admin/javascript/LeftAndMain.js');
		Requirements::javascript(FRAMEWORK_DIR .'/admin/javascript/LeftAndMain.ActionTabSet.js');
		Requirements::javascript(FRAMEWORK_DIR .'/admin/javascript/LeftAndMain.Panel.js');
		Requirements::javascript(FRAMEWORK_DIR .'/admin/javascript/LeftAndMain.Tree.js');
		Requirements::javascript(FRAMEWORK_DIR .'/admin/javascript/LeftAndMain.Content.js');
		Requirements::javascript(FRAMEWORK_DIR .'/admin/javascript/LeftAndMain.EditForm.js');
		Requirements::javascript(FRAMEWORK_DIR .'/admin/javascript/LeftAndMain.Menu.js');
		Requirements::javascript(FRAMEWORK_DIR .'/admin/javascript/LeftAndMain.Preview.js');
		Requirements::javascript(FRAMEWORK_DIR .'/admin/javascript/LeftAndMain.BatchActions.js');
		Requirements::javascript(FRAMEWORK_DIR .'/admin/javascript/LeftAndMain.FieldHelp.js');
		Requirements::javascript(FRAMEWORK_DIR .'/admin/javascript/LeftAndMain.TreeDropdownField.js');
		Requirements::javascript(FRAMEWORK_DIR .'/javascript/lang/en.js');

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