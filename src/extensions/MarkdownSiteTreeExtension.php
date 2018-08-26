<?php

namespace MadeHQ\Markdown\Extensions;

use MadeHQ\Markdown\Forms\MarkdownEditorField;

use SilverStripe\ORM\DataExtension;
use SilverStripe\View\SSViewer_FromString;
use SilverStripe\Forms\FieldList;

/**
 * Class MarkdownSiteTreeExtension
 *
 * @package markdown
 * @subpackage extensions
 */
class MarkdownSiteTreeExtension extends DataExtension
{
    public function updateCMSFields(FieldList $fields)
    {
        $fields->replaceField('Content', MarkdownEditorField::create('Content'));
    }

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
