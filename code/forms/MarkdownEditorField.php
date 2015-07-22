<?php
/**
 * Class MarkdownField
 *
 * @package markdown
 * @subpackage forms
 */
class MarkdownEditorField extends TextareaField
{
    /**
     * @var int $rows Number of rows in textarea element.
     */
    protected $rows = 20;

	private $editorConfigs = 'default';

    public function FieldHolder($properties = array()) {

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

	function getAttributes(){
		$attributes = parent::getAttributes();
		$attributes['configs'] = $this->editorConfigs;
		return $attributes;
	}




}


class MarkdownEditorField_Toolbar extends RequestHandler {


}