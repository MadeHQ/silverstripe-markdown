<?php
/**
 * Class MarkdownSiteTreeExtension
 *
 * @package markdown
 * @subpackage extensions
 */
class MarkdownSiteTreeExtension extends DataExtension
{


    /**
     * Content
     * Parses Markdown to HTML.
     *
     * @return string Parsed HTML
     */
    public function Content() {
		$content = $this->owner->dbObject('Content');
		$strContent = $content;
		if(method_exists($content, 'forTemplate')){
			$strContent = $content->forTemplate();
		}
		$data = $this->owner->toMap();
		foreach ($data as $field => $value) {
			$field = "$" . $field;
			$content = str_replace($field, $value, $content);
		}

		return $strContent;


    }



}
