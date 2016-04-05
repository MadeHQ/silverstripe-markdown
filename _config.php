<?php

define('MARKDOWN_MODULE_BASE', basename(dirname(__FILE__)));


MarkdownExtension::ReplaceHTMLFields();

if(ClassInfo::exists('CloudinaryFileField')){
    MarkdownEditorField::add_extension('MarkdownCloudinaryUpload');
}

Object::useCustomClass('HtmlEditorField_Toolbar', 'MarkdownEditorField_Toolbar', true);