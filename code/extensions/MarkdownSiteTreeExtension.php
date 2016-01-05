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
    public function ParseContent() {
		$content = $this->owner->dbObject('Content');
		$strContent = $content;
		if(method_exists($content, 'forTemplate')){
			$strContent = $content->forTemplate();
		}
		$template = SSViewer_FromString::fromString($strContent);
		return $this->owner->renderWith($template);

    }



}
