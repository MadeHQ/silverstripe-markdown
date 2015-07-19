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

    public function FieldHolder($properties = array()) {
        $this->extraClasses['stacked'] = 'stacked';

        Requirements::css(MARKDOWN_MODULE_BASE . '/css/MarkdownEditor.css');

        Requirements::javascript(MARKDOWN_MODULE_BASE . '/thirdparty/ace/ace.min.js');
        Requirements::javascript(MARKDOWN_MODULE_BASE . '/thirdparty/ace/mode-markdown.min.js');
        Requirements::javascript(MARKDOWN_MODULE_BASE . '/thirdparty/ace/theme-github.min.js');

        Requirements::javascript(MARKDOWN_MODULE_BASE . '/javascript/MarkdownEditor.js');

        return parent::FieldHolder($properties);
    }
}