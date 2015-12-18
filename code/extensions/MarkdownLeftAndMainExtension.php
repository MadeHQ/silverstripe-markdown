<?php

class MarkdownLeftAndMainExtension extends Extension {

	public function init(){
		Requirements::clear('shortcodable/javascript/editor_plugin.js');
	}


	public function onAfterInit(){
		MarkdownEditorField::include_default_js();
	}

    public function TextileEditorToolbar() {
        return MarkdownEditorField_Toolbar::create($this, "EditorToolbar");
    }

} 