<?php

class MarkdownLeftAndMainExtension extends Extension {

	public function onAfterInit(){
		MarkdownEditorField::include_default_js();
	}

    public function TextileEditorToolbar() {
        return MarkdownEditorField_Toolbar::create($this, "EditorToolbar");
    }

} 