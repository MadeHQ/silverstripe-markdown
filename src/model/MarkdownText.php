<?php

namespace MadeHQ\Markdown\Model;

use MadeHQ\Markdown\Forms\MarkdownEditorField;

use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\View\Parsers\ShortcodeParser;
use SilverStripe\Forms\TextField;
use SilverStripe\Core\Convert;

/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 7/22/15
 * Time: 8:40 AM
 * To change this template use File | Settings | File Templates.
 */
class MarkdownText extends DBText {

	private static $casting = array(
		"BigSummary" => "HTMLText",
		"ContextSummary" => "HTMLText",
		"FirstParagraph" => "HTMLText",
		"FirstSentence" => "HTMLText",
		"LimitCharacters" => "HTMLText",
		"LimitSentences" => "HTMLText",
		"Lower" => "HTMLText",
		"LowerCase" => "HTMLText",
		"Summary" => "HTMLText",
		"Upper" => "HTMLText",
		"UpperCase" => "HTMLText",
		'EscapeXML' => 'HTMLText',
		'LimitWordCount' => 'HTMLText',
		'LimitWordCountXML' => 'HTMLText',
		'NoHTML' => 'Text',
	);


	function forTemplate(){
		return $this->ParseMarkDown();
	}

	function ParseMarkDown(){
		$parser = new \ParsedownExtra();
		$value = $this->value;
		$this->extend('onBeforeParseMarkDown', $value);
		$value = $parser->text($value);
		set_error_handler(array($this, 'onError'));
		try {
			$value = ShortcodeParser::get_active()->parse($value);
		}
		catch(\Exception $e){}
		restore_error_handler();
		$this->extend('onAfterParseMarkDown', $value);
		return $value;
	}

	function onError($errno, $errstr, $errfile, $errline){

	}

	function NoHTML(){
		return strip_tags($this->ParseMarkDown());
	}

	public function scaffoldFormField($title = null, $params = null) {
		return new MarkdownEditorField($this->name, $title);
	}

	public function scaffoldSearchField($title = null, $params = null) {
		return new TextField($this->name, $title);
	}


	public function BigSummary($maxWords = 50, $plain = true, $config = null) {
		$result = '';

		// get first sentence?
		// this needs to be more robust
		$data = $this->ParseMarkDown();

		if ($plain) {
			$data = Convert::html2raw($data, true, 0, $config);
		}

		if(!$data) return '';

		$sentences = explode('.', $data);
		$count = count(explode(' ', $sentences[0]));
		// if the first sentence is too long, show only the first $maxWords words
		if($count > $maxWords) {
			return implode(' ', array_slice(explode( ' ', $sentences[0] ), 0, $maxWords)) . '...';
		}

		// add each sentence while there are enough words to do so
		do {
			$result .= trim(array_shift($sentences));
			if($sentences) {
				$result .= '. ';
				$count += count(explode(' ', $sentences[0]));
			}

			// Ensure that we don't trim half way through a tag or a link
			$brokenLink = (
					substr_count($result,'<') != substr_count($result,'>')) ||
				(substr_count($result,'<a') != substr_count($result,'</a')
				);
		} while(($count < $maxWords || $brokenLink) && $sentences && trim($sentences[0]));

		if(preg_match( '/<a[^>]*>/', $result) && !preg_match( '/<\/a>/', $result)) {
			$result .= '</a>';
		}
		return $result;
	}


	public function ContextSummary($characters = 500, $string = false, $striphtml = true, $highlight = true,
								   $prefix = "... ", $suffix = "...") {

		if(!$string) {
			// Use the default "Search" request variable (from SearchForm)
			$string = isset($_REQUEST['Search']) ? $_REQUEST['Search'] : '';
		}

		// Remove HTML tags so we don't have to deal with matching tags
		$text = $striphtml ? $this->NoHTML() : $this->ParseMarkDown();

		// Find the search string
		$position = (int) stripos($text, $string);

		// We want to search string to be in the middle of our block to give it some context
		$position = max(0, $position - ($characters / 2));

		if($position > 0) {
			// We don't want to start mid-word
			$position = max((int) strrpos(substr($text, 0, $position), ' '),
				(int) strrpos(substr($text, 0, $position), "\n"));
		}

		$summary = substr($text, $position, $characters);
		$stringPieces = explode(' ', $string);

		if($highlight) {
			// Add a span around all key words from the search term as well
			if($stringPieces) {
				foreach($stringPieces as $stringPiece) {
					if(strlen($stringPiece) > 2) {
						$summary = str_ireplace($stringPiece, "<span class=\"highlight\">$stringPiece</span>", $summary);
					}
				}
			}
		}
		$summary = trim($summary);
		if($position > 0) $summary = $prefix . $summary;
		if(strlen($this->ParseMarkDown()) > ($characters + $position)) $summary = $summary . $suffix;

		return $summary;
	}



	/**
	 * Caution: Not XML/HTML-safe - does not respect closing tags.
	 */
	public function FirstParagraph($plain = 1) {
		$strParsedText = $this->ParseMarkDown();
		// get first sentence?
		// this needs to be more robust
		if($plain && $plain != 'html') {
			$data = Convert::xml2raw($strParsedText, true);
			if(!$data) return "";
			// grab the first paragraph, or, failing that, the whole content
			$pos = strpos($data, "\n\n");
			if($pos) $data = substr($data, 0, $pos);
			return $data;
		} else {
			if(strpos($strParsedText, "</p>") === false) return $strParsedText;
			$data = substr($strParsedText, 0, strpos($strParsedText, "</p>") + 4);
			if(strlen($data) < 20 && strpos($strParsedText, "</p>", strlen($data))) {
				$data = substr($strParsedText, 0, strpos( $strParsedText, "</p>", strlen($data)) + 4 );
			}
			return $data;
		}
	}


	/**
	 * Caution: Not XML/HTML-safe - does not respect closing tags.
	 */
	public function FirstSentence() {
		$strParsedText = $this->ParseMarkDown();
		$paragraph = Convert::xml2raw( $strParsedText );
		if( !$paragraph ) return "";

		$words = preg_split('/\s+/', $paragraph);
		foreach ($words as $i => $word) {
			if (preg_match('/(!|\?|\.)$/', $word) && !preg_match('/(Dr|Mr|Mrs|Ms|Miss|Sr|Jr|No)\.$/i', $word)) {
				return implode(' ', array_slice($words, 0, $i+1));
			}
		}

		/* If we didn't find a sentence ending, use the summary. We re-call rather than using paragraph so that
		 * Summary will limit the result this time */
		return $this->Summary(20);
	}

}
