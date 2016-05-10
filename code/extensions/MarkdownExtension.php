<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 7/22/15
 * Time: 8:47 AM
 * To change this template use File | Settings | File Templates.
 */

class MarkdownExtension extends DataExtension {

    private static $replace_html_fields = true;
    private static $db_field_cache = array();
    private static $disable_markdown_fields = false;

    protected static function without_markdown_fields($callback) {
        $before = self::$disable_markdown_fields;
        self::$disable_markdown_fields = true;
        $result = $callback();
        self::$disable_markdown_fields = $before;
        return $result;
    }


    public static function get_db_fields_for_class($class)
    {
        if(isset(self::$db_field_cache[$class])) {
            return self::$db_field_cache[$class];
        }
        $db = self::without_markdown_fields(function() use ($class) {
            return DataObject::custom_database_fields($class);
        });
        self::$db_field_cache[$class] = $db;
        return self::$db_field_cache[$class];
    }


    public static function get_extra_config($class, $extension, $args) {
        if(!self::$replace_html_fields) return array();
        if(self::$disable_markdown_fields) return array();

        $config = Config::inst();
        // Merge all config values for subclasses
        foreach (ClassInfo::subclassesFor($class) as $subClass) {
            $db = self::get_db_fields_for_class($subClass);
            $updated = false;
            foreach($db as $field => $type){
                if(strpos($type, 'HTMLText') !== false){
                    $updated = true;
                    $db[$field] =  str_replace($type, 'HTMLText', 'MarkdownText');
                }
                if(strpos($type, 'HTMLVarchar') !== false){
                    $updated = true;
                    $db[$field] =  str_replace($type, 'HTMLVarchar', 'MarkdownVarchar');
                }
            }
            if($updated){
               $config->update($subClass, 'db', $db);
            }
        }
        // Force all subclass DB caches to invalidate themselves since their db attribute is now expired
        DataObject::reset();
    }


    public function updateCMSFieldSecondary(FieldList $fields){
        $this->updateCMSFields($fields);
    }


    public function updateCMSFields(FieldList $fields){
        if(Config::inst()->get('MarkdownExtension', 'replace_html_fields')){
            foreach($fields->dataFields() as $field) {
                if($field instanceof HtmlEditorField) {
                    $attributes = $field->getAttributes();

                    $fields->replaceField($field->getName(),
                        MarkdownEditorField::create($field->getName(), $field->Title())->setRows($attributes['rows']));
                }
            }
        }
    }


} 