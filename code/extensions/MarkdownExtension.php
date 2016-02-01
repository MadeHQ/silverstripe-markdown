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

    public static function ReplaceHTMLFields(){
        if(Config::inst()->get('MarkdownExtension', 'replace_html_fields')){
            $classes = ClassInfo::subclassesFor('DataObject');
            foreach($classes as $className){
                if($db = Config::inst()->get($className, 'db')){
                    if(in_array('HTMLText', $db)){
                        $updateDB = array();
                        foreach($db as $field => $type){
                            $newType = $type;
                            if(strpos($type, 'HTMLText') !== false){
                                $newType = str_replace($type, 'HTMLText', 'MarkdownText');
                            }
                            if(strpos($type, 'HTMLVarchar') !== false){
                                $newType = str_replace($type, 'HTMLVarchar', 'MarkdownVarchar');
                            }

                            $updateDB[$field] = $newType;
                        }

                        Config::inst()->update($className, 'db', $updateDB);
                    }
                }
            }
        }
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