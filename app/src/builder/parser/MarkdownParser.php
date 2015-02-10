<?php

namespace kalat\builder\parser;
use Michelf\MarkdownExtra;
use kalat\builder\SiteBuilder;

class MarkdownParser extends MarkdownExtra{
	
	private $shortCodes = array();
	
	public function __construct(){
		
		//document_gamutは全体で呼ばれる
		/*
		$this->document_gamut += array(
			"doDocumentGamutTest" => 5
		);
		*/
		
		//span_gamutはplain_textの中だけ
		$this->span_gamut += array(
			"doShortTag"		=> 100,	//大きい数字にしないとhtmlをエスケープされてしまう
			"doFormatLikeGFM"	=> 101
		);
		$this->document_gamut += array(
			"doGenerateIndex"	=> 100
		);
		
		$this->shortCodes = SiteBuilder::getCurrentBuilder()->getShortCodes();
		
		
		$this->code_attr_on_pre = true;
		
		parent::__construct();
	}
	
	public static function convert($text){
		return self::defaultTransform($text);
	}
	
	protected function setup() {
		parent::setup();
		$this->indexList = array();
	}
	
	private $shortCodeRegExp = null;
	
	protected function doShortTag($text) {
		#
		# Replace footnote references in $text [^id] with a special text-token
		# which will be replaced by the actual footnote marker in appendFootnotes.
		if(!$this->shortCodeRegExp){
			$names = array_keys($this->shortCodes);
			$tagregexp = join( '|', array_map('preg_quote', $names) );
			
			$regexp =  '\\[(\\[?)'. "(${tagregexp})" . '(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
			
			$this->shortCodeRegExp = "/" . $regexp . "/s";
		}
		$text = preg_replace_callback($this->shortCodeRegExp, array($this, "_doShortTag"), $text);
		
		return $text;
	}
	
	function _doShortTag($match){
		
		if($match[1] == "[" && $match[6] == "]"){
			return substr($match[0], 1, -1);
		}
		
		$tagName = $match[2];
		$args = $this->_parseShortTagAttributes($match[3]);
		$inner = $match[5];
		
		$text = call_user_func($this->shortCodes[$tagName], $inner, $args, $this);
		
		return $this->hashPart($text);
	}
	
	function _parseShortTagAttributes($text) {
		$atts = array();
		$pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
		$text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
		if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) ) {
			foreach ($match as $m) {
				if (!empty($m[1])){
					$atts[strtolower($m[1])] = stripcslashes($m[2]);
				}elseif (!empty($m[3])){
					$atts[strtolower($m[3])] = stripcslashes($m[4]);
				}elseif (!empty($m[5])){
					$atts[strtolower($m[5])] = stripcslashes($m[6]);
				}elseif (isset($m[7]) and strlen($m[7])){
					$atts[] = stripcslashes($m[7]);
				}elseif (isset($m[8])){
					$atts[] = stripcslashes($m[8]);
				}
			}
		} else {
			$atts = ltrim($text);
		}
		return $atts;
	}
	
	public function doShortCodeIndex($text, $args){
		$tag = (isset($args["tag"])) ? $args["tag"] : "li";
		return "@@REPLACE_CONTENT_INDEX($tag)@@";	//INDEXに置換する
	}
	
	/**
	 * GFM同様に段落中の改行を処理する
	 * @param unknown $text
	 * @return mixed
	 */
	protected function doFormatLikeGFM($text) {
		
		$text = preg_replace_callback('#\n#', array($this, "_doNewLineTrailBreak"), $text);
		$text = preg_replace_callback('#(~{2})(.*)\1#', array($this, "_doStrikethrough"), $text);
		
		return $text;
	}
	
	function _doNewLineTrailBreak($match){
		return "<br$this->empty_element_suffix\n";
	}
	
	function _doStrikethrough($match){
		return "<del>" . $match[2] . "</del>";
	}
	
	function doGenerateIndex($text){
		
		$indexList = $this->indexList;
		
		$text = preg_replace_callback("#@@REPLACE_CONTENT_INDEX\((.*)\)@@#", function($match) use ($indexList){
			
			$indexListText = array();
			$tag = $match[1];
			
			foreach($this->indexList as $array){
				$indexListText[] = '<'.$tag.'><a href="#' . $array[2] . '">' . $array[1] . '</a></'.$tag.'>';
			}
			$indexListText = implode("\n", $indexListText);
			
			return $indexListText;
		}, $text);
		
		return $text;
	}
	
	/* override */
	
	protected function doFencedCodeBlocks($text) {
	
		#
		# Code Blockは二種類で行う追加
		#
		# ~~~
		# Code block
		# ~~~
		#
		# ```
		# Code block
		# ```
		#
		
		$less_than_tab = $this->tab_width;

		$text = preg_replace_callback('{
				(?:\n|\A)
			# 1: Opening marker
			(
				~{3,}|`{3,} # Marker: three tilde or more.
			)
			[ ]*
			(?:
				\.?([-_:a-zA-Z0-9]+) # 2: standalone class name
			|
				'.$this->id_class_attr_catch_re.' # 3: Extra attributes
			)?
			[ ]* \n # Whitespace and newline following marker.

			# 4: Content
			(
				(?>
					(?!\1 [ ]* \n)	# Not a closing marker.
					.*\n+
				)+
			)

			# Closing marker.
			\1 [ ]* \n
		}xm',
		array(&$this, '_doFencedCodeBlocks_callback'), $text);

		return $text;
	}
	
	private $indexList = array();

	protected function _doHeaders_callback_setext($matches) {
		# Terrible hack to check we haven't found an empty list item.
		if ($matches[2] == '-' && preg_match('{^-(?: |$)}', $matches[1])){
			return $matches[0];
		}
		
		$level = $matches[2]{0} == '=' ? 1 : 2;
		return $this->_doHeaderImpl($level, $matches[1]);
		
	}
	protected function _doHeaders_callback_atx($matches) {
		$level = strlen($matches[1]);
		return $this->_doHeaderImpl($level, $matches[2]);
	}
	
	private function _doHeaderImpl($level, $text){
		/*
		 * h*系は自動的にanchorをつける
		*/
		
		$anchorName = urlencode($text);
		if(isset($this->indexList[$anchorName]))$anchorName .= "-" . count($this->indexList);
		$anchor = '<span id="'.$anchorName.'" class="anchor"></span>';
		$this->indexList[$anchorName] = [
			$level, $text, $anchorName
		];
		
		$block = "<h$level>".$anchor . $this->runSpanGamut($text)."</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}
}