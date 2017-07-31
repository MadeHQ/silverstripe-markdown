<?php
/**
 * Class MarkdownField
 *
 * @package markdown
 * @subpackage forms
 */
class MarkdownEditorField extends TextareaField {

    /**
     * @var int $rows Number of rows in textarea element.
     */
    protected $rows = 20;

	private $editorConfigs = 'default';

    public function FieldHolder($properties = array()) {
        $this->extraClasses['stacked'] = 'stacked';

		$this->include_js();
		Requirements::css(MARKDOWN_MODULE_BASE . '/thirdparty/font-awesome-4.3.0/css/font-awesome.min.css');
		Requirements::css(MARKDOWN_MODULE_BASE . '/css/MarkdownEditor.css');
		Requirements::css(MARKDOWN_MODULE_BASE . '/thirdparty/editor/simplemde.min.css');

		if(0 && Director::isDev()){
			Requirements::javascript(MARKDOWN_MODULE_BASE . '/thirdparty/editor/sourcefiles/codemirror/codemirror.js');
			Requirements::javascript(MARKDOWN_MODULE_BASE . '/thirdparty/editor/sourcefiles/simplemde.js');
		}
		else{

			Requirements::javascript(MARKDOWN_MODULE_BASE . '/thirdparty/editor/simplemde.min.js');
		}
		Requirements::javascript(MARKDOWN_MODULE_BASE . '/javascript/MarkdownEditorField.js');
        Requirements::javascript(MARKDOWN_MODULE_BASE . '/javascript/MarkDownShortCode.js');


        $this->extend("updateFieldHolder");
        return parent::FieldHolder($properties);
    }

	public function setEditorConfigs($editorConfigs){
		$this->editorConfigs = $editorConfigs;
	}

	function getEditorConfigs(){
		return $this->editorConfigs;
	}

	public function include_js() {
		$configObj = MarkdownEditorConfig::get($this->editorConfigs);
		Requirements::insertHeadTags('<script>var markdownEditorConfigs = {};</script>', 'MarkdownEditorConfigHolder');
		Requirements::insertHeadTags('<script>' . $configObj->generateJS() . '</script>', 'MarkdownEditorConfig_' . $this->editorConfigs);
	}

	public static function include_default_js(){
		$configObj = MarkdownEditorConfig::get('default');
		Requirements::insertHeadTags('<script>var markdownEditorConfigs = {};</script>', 'MarkdownEditorConfigHolder');
		Requirements::insertHeadTags('<script>' . $configObj->generateJS() . '</script>', 'MarkdownEditorConfig_default');
	}

	function getAttributes(){
		$attributes = parent::getAttributes();
		$attributes['configs'] = $this->editorConfigs;
		return $attributes;
	}




}


class MarkdownEditorField_Toolbar extends RequestHandler {

    private static $allowed_actions = array(
        'LinkForm',
        'MediaForm',
        'viewfile',
        'getanchors'
    );

    /**
     * @var string
     */
    protected $templateViewFile = 'HtmlEditorField_viewfile';

    protected $controller, $name;

    public function __construct($controller, $name) {
        parent::__construct();

        $this->controller = $controller;
        $this->name = $name;
    }

    public function forTemplate() {
        return sprintf(
            '<div id="cms-editor-dialogs" data-url-linkform="%s" ></div>',
            Controller::join_links($this->controller->Link(), $this->name, 'LinkForm', 'forTemplate')
        );
    }

    /**
     * Searches the SiteTree for display in the dropdown
     *
     * @return callback
     */
    public function siteTreeSearchCallback($sourceObject, $labelField, $search) {
        return DataObject::get($sourceObject)->filterAny(array(
            'MenuTitle:PartialMatch' => $search,
            'Title:PartialMatch' => $search
        ));
    }

    public function LinkForm(){
        $siteTree = new TreeDropdownField('internal', _t('HtmlEditorField.PAGE', "Page"),
            'SiteTree', 'ID', 'MenuTitle', true);
        // mimic the SiteTree::getMenuTitle(), which is bypassed when the search is performed
        $siteTree->setSearchFunction(array($this, 'siteTreeSearchCallback'));

        $numericLabelTmpl = '<span class="step-label"><span class="flyout">%d</span><span class="arrow"></span>'
            . '<strong class="title">%s</strong></span>';
        $form = new Form(
            $this->controller,
            "{$this->name}/LinkForm",
            new FieldList(
                $headerWrap = new CompositeField(
                    new LiteralField(
                        'Heading',
                        sprintf('<h3 class="htmleditorfield-mediaform-heading insert">%s</h3>',
                            _t('HtmlEditorField.LINK', 'Insert Link'))
                    )
                ),
                $contentComposite = new CompositeField(
                    new OptionsetField(
                        'LinkType',
                        sprintf($numericLabelTmpl, '1', _t('MarkdownEditorField.LINKTO', 'Link to')),
                        array(
                            'internal' => _t('MarkdownEditorField.LINKINTERNAL', 'Page on the site'),
                            'external' => _t('MarkdownEditorField.LINKEXTERNAL', 'Another website'),
                            'anchor' => _t('MarkdownEditorField.LINKANCHOR', 'Anchor on this page'),
                            'email' => _t('MarkdownEditorField.LINKEMAIL', 'Email address'),
                        ),
                        'internal'
                    ),
                    new LiteralField('Step2',
                        '<div class="step2">'
                        . sprintf($numericLabelTmpl, '2', _t('HtmlEditorField.DETAILS', 'Details')) . '</div>'
                    ),
                    $siteTree,
                    new TextField('external', _t('MarkdownEditorField.URL', 'URL'), 'http://'),
                    new EmailField('email', _t('MarkdownEditorField.EMAIL', 'Email address')),
                    new TreeDropdownField('file', _t('MarkdownEditorField.FILE', 'File'), 'File', 'ID', 'Title', true),
                    new TextField('Anchor', _t('MarkdownEditorField.ANCHORVALUE', 'Anchor')),
                    new TextField('LinkText', _t('MarkdownEditorField.LINKTEXT', 'Link text')),
                    new TextField('Description', _t('MarkdownEditorField.LINKDESCR', 'Link title')),
                    new CheckboxField('TargetBlank',
                        _t('MarkdownEditorField.LINKOPENNEWWIN', 'Open link in a new window?')),
                    new HiddenField('Locale', null, $this->controller->Locale)
                )
            ),
            new FieldList(

                FormAction::create('insert', _t('HtmlEditorField.BUTTONINSERTLINK', 'Insert link'))
                    ->addExtraClass('ss-ui-action-constructive')
                    ->setAttribute('data-icon', 'accept')
                    ->setUseButtonTag(true)
            )
        );

        $headerWrap->addExtraClass('CompositeField composite cms-content-header nolabel ');
        $contentComposite->addExtraClass('ss-insert-link content');

        $form->unsetValidator();
        $form->loadDataFrom($this);
        $form->addExtraClass('markdownfield-form markdowneditorfield-linkform cms-dialog-content');

        $this->extend('updateLinkForm', $form);

        return $form;
    }

    /**
     * Find all anchors available on the given page.
     *
     * @return string
     * @throws SS_HTTPResponse_Exception
     */
    public function getanchors() {
        $id = (int)$this->request->getVar('PageID');
        $anchors = array();

        if (($page = Page::get()->byID($id)) && !empty($page)) {
            if (!$page->canView()) {
                throw new SS_HTTPResponse_Exception(
                    _t(
                        'HtmlEditorField.ANCHORSCANNOTACCESSPAGE',
                        'You are not permitted to access the content of the target page.'
                    ),
                    403
                );
            }

            // Similar to the regex found in HtmlEditorField.js / getAnchors method.
            if (preg_match_all("/name=\"([^\"]+?)\"|name='([^']+?)'/im", $page->Content, $matches)) {
                $anchors = $matches[1];
            }

        } else {
            throw new SS_HTTPResponse_Exception(
                _t('HtmlEditorField.ANCHORSPAGENOTFOUND', 'Target page not found.'),
                404
            );
        }

        return json_encode($anchors);
    }

}
