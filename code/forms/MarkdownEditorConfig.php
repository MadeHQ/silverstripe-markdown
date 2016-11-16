<?php

namespace MadeHQ\Markdown\Forms;

use SilverStripe\Core\Convert;
use SilverStripe\Core\Config\Config;

/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 7/22/15
 * Time: 11:22 AM
 * To change this template use File | Settings | File Templates.
 */

class MarkdownEditorConfig {

	private static $configs = array();

	private $identifier = '';

	protected $plugins = array(
		'spellchecker',
		'autosave',
	);

	public function __construct($identifier){
		$this->identifier = $identifier;
	}

	public static function get($identifier = 'default') {
		if (!array_key_exists($identifier, self::$configs)) self::$configs[$identifier] = new MarkdownEditorConfig($identifier);
		return self::$configs[$identifier];
	}

    protected function getButtons()
    {
        return Config::inst()->get('MadeHQ\Markdown\Forms\MarkdownEditorConfig', 'buttons', Config::UNINHERITED);
    }

    protected function getButtonConfigs()
    {
        return Config::inst()->get('MadeHQ\Markdown\Forms\MarkdownEditorConfig', 'button_configs');
    }

	public function generateJS(){
		$arrConfigs = array(
			'status'			=> array(
				'lines',
				'words',
				'cursor'
			),
			'toolbar'			=> array()
		);
		$buttonConfigs = $this->getButtonConfigs();
		foreach($this->getButtons() as $button){
			if(array_key_exists($button, $buttonConfigs)){
				$arrConfigs['toolbar'][] = $buttonConfigs[$button];
			}

		}

		$strJSON = Convert::array2json($arrConfigs);
		return sprintf(
			'markdownEditorConfigs.%s = %s;',
			$this->identifier,
			$strJSON
		);
	}

}
