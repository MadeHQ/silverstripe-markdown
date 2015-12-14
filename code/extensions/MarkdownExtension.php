<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 7/22/15
 * Time: 8:47 AM
 * To change this template use File | Settings | File Templates.
 */

class MarkdownExtension extends DataExtension {

	public static function ReplaceHTMLFields(){

		if(isset($_REQUEST['flush']) && $_REQUEST['flush'] == 'all'){
			$classes = ClassInfo::subclassesFor('DataObject');
			$configManifest = new SS_ConfigStaticManifest(BASE_PATH, false, true);
			$static = $configManifest->getStatics();

			$arrUpdated = array();

			foreach($static as $className => $options){
				if(in_array($className, $classes) && isset($options['db']) && isset($options['db']['value'])){
					$bUpdated = false;
					foreach($options['db']['value'] as $field => $type){
						$newType = $type;
						if(strpos($type, 'HTMLText') !== false){
							$newType = str_replace($type, 'HTMLText', 'MarkdownText');
							$bUpdated = true;
						}
						if(strpos($type, 'HTMLVarchar') !== false){
							$newType = str_replace($type, 'HTMLVarchar', 'MarkdownVarchar');
							$bUpdated = true;
						}
						$options['db']['value'][$field] = $newType;
					}

					if($bUpdated == true){
						$arrUpdated[$className] = $options;
						Config::inst()->update($className, 'db', $options['db']['value']);
					}

				}
			}

			if(count($arrUpdated)){
				$cacheClass = defined('SS_MANIFESTCACHE') ? SS_MANIFESTCACHE : 'ManifestCache_File';
				$cache = new $cacheClass('staticmanifest');
				$pathKey = sha1(BASE_PATH);

				$keysets = array();

				foreach ($arrUpdated as $class => $details) {
					$key = sha1($class);
					$keysets[$key][$class] = $details;
				}

				foreach ($keysets as $key => $details) {
					$cache->save($details, $pathKey.'_'.$key);
				}
			}
		}

	}

	public function updateCMSFieldSecondary(FieldList $fields){
		$this->updateCMSFields($fields);
	}

	public function updateCMSFields(FieldList $fields){
        foreach($fields->dataFields() as $field) {
            if($field instanceof HtmlEditorField) {
                $attributes = $field->getAttributes();

                $fields->replaceField($field->getName(),
                    MarkdownEditorField::create($field->getName(), $field->Title())->setRows($attributes['rows']));
            }
        }
	}

} 