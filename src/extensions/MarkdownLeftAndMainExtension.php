<?php

namespace MadeHQ\Markdown\Extensions;

use MadeHQ\Markdown\Forms\MarkdownEditorField;
use MadeHQ\Markdown\Forms\MarkdownEditorField_Toolbar;
use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;

class MarkdownLeftAndMainExtension extends Extension
{

    public function init()
    {
        Requirements::clear('shortcodable/javascript/editor_plugin.js');
    }


    public function onAfterInit()
    {
        MarkdownEditorField::include_default_js();
    }

    public function TextileEditorToolbar() 
    {
        return MarkdownEditorField_Toolbar::create($this, "EditorToolbar");
    }

}
