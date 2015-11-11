<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 7/22/15
 * Time: 11:22 AM
 * To change this template use File | Settings | File Templates.
 */

class MarkdownEditorConfig {

	private static $configs = array();

	private static $button_configs = array(
		'|'				=> '|',
		'header-1'			=> array(
			'name'			=> 'header-1',
			'action'		=> 'drawMarkdownH1',
			'className'		=> 'fa fa-header h1',
			'title'			=> 'Header 1',
		),
		'header-2'			=> array(
			'name'			=> 'header-2',
			'action'		=> 'drawMarkdownH2',
			'className'		=> 'fa fa-header h2',
			'title'			=> 'Header 2',
		),
		'header-3'			=> array(
			'name'			=> 'header-3',
			'action'		=> 'drawMarkdownH3',
			'className'		=> 'fa fa-header h3',
			'title'			=> 'Header 3',
		),
		'header-4'			=> array(
			'name'			=> 'header-4',
			'action'		=> 'drawMarkdownH4',
			'className'		=> 'fa fa-header h4',
			'title'			=> 'Header 4',
		),
		'header-5'			=> array(
			'name'			=> 'header-5',
			'action'		=> 'drawMarkdownH5',
			'className'		=> 'fa fa-header h5',
			'title'			=> 'Header 5',
		),
		'header-6'			=> array(
			'name'			=> 'header-6',
			'action'		=> 'drawMarkdownH6',
			'className'		=> 'fa fa-header h6',
			'title'			=> 'Header 6',
		),
		'bold'			=> array(
			'name'			=> 'bold',
			'action'		=> 'toggleBold',
			'className'		=> 'fa fa-bold',
			'title'			=> 'Bold (Ctrl+B)',
		),
		'italic'		=> array(
			'name'			=> 'italic',
			'action'		=> 'toggleItalic',
			'className'		=> 'fa fa-italic',
			'title'			=> 'Italic (Ctrl+I)',
		),
		'quote'			=> array(
			'name' 			=> 'quote',
			'action' 		=> 'toggleBlockquote',
			'className' 	=> 'fa fa-quote-left',
			'title' 		=> 'Quote (Ctrl+)',
		),
		'unordered-list'=> array(
			'name' 			=> 'unordered-list',
			'action' 		=> 'toggleUnorderedList',
			'className' 	=> 'fa fa-list-ul',
			'title' 		=> 'Generic List (Ctrl+L)',
		),
		'ordered-list'	=> array(
			'name' 			=> 'ordered-list',
			'action' 		=> 'toggleOrderedList',
			'className' 	=> 'fa fa-list-ol',
			'title' 		=> 'Numbered List (Ctrl+Alt+L)',
		),
		'link'			=> array(
			'name' 			=> 'link',
			'action' 		=> 'drawLink',
			'className' 	=> 'fa fa-link',
			'title' 		=> 'Create Link (Ctrl+K)',
		),
		'image'			=> array(
			'name' 			=> 'image',
			'action' 		=> 'drawImage',
			'className' 	=> 'fa fa-picture-o',
			'title' 		=> 'Insert Image (Ctrl+Alt+I)',
		),
		'preview'		=> array(
			'name' 			=> 'preview',
			'action' 		=> 'togglePreview',
			'className' 	=> 'fa fa-eye',
			'title' 		=> 'Toggle Preview (Ctrl+P)',
		),
		'guide'			=> array(
			'name' 			=> 'guide',
			'action' 		=> 'http://nextstepwebs.github.io/simplemde-markdown-editor/markdown-guide',
			'className' 	=> 'fa fa-question-circle',
			'title' 		=> 'Markdown Guide',
		),
		'shortcodable'	=> array(
			'name' 			=> 'shortcodable',
			'action' 		=> 'shortCode',
			'className' 	=> 'fa fa-eye',
			'title' 		=> 'Short Code',
		)

	);

	private $identifier = '';

	protected $plugins = array(
		'spellchecker',
		'autosave',
	);

	protected $buttons = array('header-1','header-2','header-3','header-4','header-5','header-6','|', 'bold','italic','|','quote','unordered-list',
			'ordered-list','|','link','image','|','preview','guide','shortcodable'
	);

	public function __construct($identifier){
		$this->identifier = $identifier;
	}


	public static function get($identifier = 'default') {
		if (!array_key_exists($identifier, self::$configs)) self::$configs[$identifier] = new MarkdownEditorConfig($identifier);
		return self::$configs[$identifier];
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

		$buttonConfigs = Config::inst()->get('MarkdownEditorConfig', 'button_configs');
		foreach($this->buttons as $button){
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