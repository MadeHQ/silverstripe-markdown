<?php
/**
 * Class MarkdownSiteTreeExtension
 *
 * @package markdown
 * @subpackage extensions
 */
class MarkdownSiteTreeExtension extends DataExtension
{
    private static $db = array(
        "Content" => "Text"
    );

    /**
     * updateCMSFields
     * Replaces all instances of HtmlEditorField with MarkdownField.
     *
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields) {
        foreach($fields->dataFields() as $field) {
            if($field instanceof HtmlEditorField) {
                $markdownEditorField = new MarkdownEditorField($field->name, $field->title, 30);
                $fields->replaceField($field->name, $markdownEditorField);
            }
        }
    }

    /**
     * Content
     * Parses Markdown to HTML.
     *
     * @return string Parsed HTML
     */
    public function Content() {
        $content = $this->owner->Content;
        // Replace variables with CMS content.
        $data = $this->owner->toMap();
        foreach ($data as $field => $value) {
            $field = "$" . $field;
            $content = str_replace($field, $value, $content);
        }
        $parser = new Parsedown();
        return $parser->text($content);
    }
}
