<?php

namespace MadeHQ\Markdown\Model;

use MadeHQ\Markdown\Forms\MarkdownEditorField;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\View\Parsers\ShortcodeParser;
use SilverStripe\Forms\TextField;

/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 7/22/15
 * Time: 8:41 AM
 * To change this template use File | Settings | File Templates.
 */

class MarkdownVarchar extends DBVarchar
{

    function forTemplate()
    {
        $parser = new \ParsedownExtra();
        $value = ShortcodeParser::get_active()->parse($this->value);
        return $parser->text($value);
    }

    public function scaffoldFormField($title = null, $params = null) 
    {
        return new MarkdownEditorField($this->name, $title);
    }

    public function scaffoldSearchField($title = null, $params = null) 
    {
        return new TextField($this->name, $title);
    }

}
