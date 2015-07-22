<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 7/22/15
 * Time: 8:41 AM
 * To change this template use File | Settings | File Templates.
 */

class MarkdownVarchar extends Varchar {

	function forTemplate(){
		$parser = new Parsedown();
		$value = ShortcodeParser::get_active()->parse($this->value);
		return $parser->text($value);
	}

	public function scaffoldFormField($title = null, $params = null) {
		return new MarkdownEditorField($this->name, $title);
	}

	public function scaffoldSearchField($title = null, $params = null) {
		return new TextField($this->name, $title);
	}

} 