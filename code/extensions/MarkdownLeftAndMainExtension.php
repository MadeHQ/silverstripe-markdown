<?php

class MarkdownLeftAndMainExtension extends Extension {

    public function TextileEditorToolbar() {
        return MarkdownEditorField_Toolbar::create($this, "EditorToolbar");
    }

} 