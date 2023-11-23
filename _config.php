<?php

define('MARKDOWN_MODULE_BASE', basename(dirname(__FILE__)));

if(ClassInfo::exists('CloudinaryFileField')){
    MarkdownEditorField::add_extension('MarkdownCloudinaryUpload');
}

SS_Object::useCustomClass('HtmlEditorField_Toolbar', 'MarkdownEditorField_Toolbar', true);
