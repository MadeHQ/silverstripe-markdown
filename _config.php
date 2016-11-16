<?php

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Object;
use MadeHQ\Markdown\Forms\MarkdownEditorField;

define('MARKDOWN_MODULE_BASE', basename(dirname(__FILE__)));

if(ClassInfo::exists('MadeHQ\Cloudinary\Forms\File')){
    MarkdownEditorField::add_extension('MadeHQ\Markdown\Extensions\MarkdownCloudinaryUpload');
}
