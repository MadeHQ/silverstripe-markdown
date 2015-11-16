<?php

define('MARKDOWN_MODULE_BASE', basename(dirname(__FILE__)));


MarkdownExtension::ReplaceHTMLFields();

MarkdownEditorField::include_default_js();

if(ClassInfo::exists('CloudinaryUploadField')){
    MarkdownEditorField::add_extension('MarkdownCloudinaryUpload');
}

Object::useCustomClass('HtmlEditorField_Toolbar', 'MarkdownEditorField_Toolbar', true);