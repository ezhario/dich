<?
class	CTemplate
{
	protected $_oData = array();
	
	public function Set($psField, $poValue)
	{
		$this->_oData[$psField] = $poValue;
	
	}
	
	public function IsFieldSet($psField)
	{
		return isset($this->_oData[$psField]);
	}	
	
	public function SetArray($poArray)
	{
		if (is_array($poArray))
			foreach ($poArray as $lsKey => $lsValue)
				$this->Set($lsKey, $lsValue);
	}	
	
	public function Get($psField)
	{
		return isset($this->_oData[$psField])?$this->_oData[$psField] : null;
	}
	
	public function Preprocess( $psFileName)
	{
		return $this->PreprocessText( "?>".file_get_contents(Templates::ResolveName( $psFileName ))."<?" );
	}
	
	public function Process($psFileName)
	{
		$lsCode = "?>".file_get_contents(Templates::ResolveName( $psFileName ))."<?";

		return $this->ProcessFromText($lsCode);
	}
	
	public function	ProcessFromText($psText)
	{
		Templates::Down();
		
		$lsExecutionCode = $this->EncodeMacroDefinintionsIntoNativeCode( $this->BeforeProcess($psText) );
		
		ob_start();
		
		eval( $lsExecutionCode );
		$lsCode = $this->AfterProcess( ob_get_contents() );
		ob_end_clean();
		
		Templates::Up();
		
		return	new TemplateResult( $lsCode, $results, $blocks, $lsExecutionCode );
	}

	public function	PreprocessText($psText)
	{
		return	new TemplateResult( "", array(), array(), $this->EncodeMacroDefinintionsIntoNativeCode( $this->BeforeProcess($psText) ) );
	}

	public function	ProcessFromCache($paCache)
	{
		Templates::Down();
		
		ob_start();
		eval( Templates::Source( $paCache ) );
		$lsCode = $this->AfterProcess( ob_get_contents() );
		ob_end_clean();
		
		Templates::Up();
		
		return	new TemplateResult( $lsCode, $results, $blocks, "" );
	}
	
	public function	BeforeProcess($psCode) 
	{ 
		return $psCode; 
	}
	
	public function	AfterProcess($psCode) { return $psCode; }
	
	protected function EncodeMacroDefinintionsIntoNativeCode($psCode)
	{
		$loEncoders = array(
			"EncoderFieldSubstitute" =>
			'/@@
				([a-zA-Z_]{1}[a-zA-Z_0-9]*){1}((?:.)*?)
			@@/ix',			

			"EncoderI18n" =>
			'/\{\{i18n \s* ([^\}]*) \s* \}\}/ix',
		
			"EncoderValueSubstitute" =>
			'/%%
				([a-zA-Z_]{1}[a-zA-Z_0-9]*){1}((?:.)*?)
			%%/ix',			
			
			"EncoderEndFor" =>
			'/\{\{endfor\}\}/ix',

			"EncoderEndWhile" =>
			'/\{\{endwhile\}\}/ix',
			
			"EncoderEndIf" =>
			'/\{\{endif\}\}/ix',
			
			"EncoderElse" =>
			'/\{\{else\}\}/ix',

			"EncoderForeachAsKV" =>
			'/\{\{foreach \s+ ([^\}]+) \s+ as \s+ ([^\}]+) \s+ in \s+ ([^\}]+) \s* \}\}/ix',
			
			"EncoderForeach" =>
			'/\{\{foreach \s+ ([^\}]+) \s+ in \s+ ([^\}]+) \s* \}\}/ix',
			
			"EncoderIf" =>
			'/\{\{if \s* ([^\}]*) \s* \}\}/ix',

			"EncoderWhile" =>
			'/\{\{while \s* ([^\}]*) \s* \}\}/ix',

			"EncoderBeginChunk" => 
			'/[\{]{2}inline \s+ 
				([A-Za-z_]{1}[A-Za-z0-9_]*?) \s* 
			[\}]{2}/ix',

			"EncoderEndChunk" => 
			'/\{\{endinline\}\}/ix',

			"EncoderBeginBlock" => 
			'/[\{]{2}block \s+ 
				([A-Za-z_]{1}[A-Za-z0-9_]*?) \s* 
			[\}]{2}/ix',

			"EncoderEndBlock" => 
			'/\{\{endblock\}\}/ix',
			
			"EncoderMacroInsert" => 
			'/[\{]{2}[M]{1} \s+ 
				([^:]+?) \s* 
				(?: [:]{1}  
					\s*
					(.*)
				){0,1}
			[\}]{2}/ix',

			"EncoderStencilInsert" => 
			'/[\{]{2}[S]{1}[T]{1} \s+ 
				([^:]+?) \s* 
				(?: [:]{1}  
					\s*
					(.*)
				){0,1}
			[\}]{2}/ix',

			"EncoderChunkInsert" => 
			'/[\{]{2}[I]{1} \s+ 
				([A-Za-z_]{1}[A-Za-z0-9_]*?) \s* 
				(?: [:]{1}  
					\s*
					(.*)
				){0,1}
			[\}]{2}/ix',

			"EncoderDirectOutput" =>
			'/\{\{\{ ([^\}]*) \}\}\}/ix',

			"EncoderResultOutput" =>
			'/\{\{result\s+([A-Za-z_]{1}[A-Za-z0-9_]*?)\s+([^\}]+) \s* [\}]{2}/ix',

			"EncoderOutput" =>
			'/\{\{ ([^\}]*) \}\}/ix',
			
			"EncoderQuote" =>
			'/(@\\\\@|%\\\\%|\{\\\\\{\\\\\{|\}\\\\\}\\\\\}|\{\\\\\{|\}\\\\\})/ix'
		);

		$lsCode = $psCode;

		// Припишем список параметров для избавления от $this->Get
		$lsParamCode = '$parameters = array(); $blocks = array(); $results = array(); ';
		
		foreach (array_keys($this->_oData) as $k)
			$lsParamCode .= '$parameters["'. addslashes($k) .'"] = $this->Get("' . addslashes($k) . '");';
			
		$lsCode	= $lsParamCode . $lsCode;
		
		// Выполним макросоту
		foreach ($loEncoders as $lsKey => $lsValue)
		{
			$lsCode = preg_replace_callback( $lsValue, array( &$this, $lsKey) , $lsCode );
		}
		
		$lsCode .= '
			$tmp_blocks = array();
			foreach ($blocks as $block_name=>$block_function)
			{
				ob_start();
				$block_function($parameters);
				$tmp_blocks[$block_name] = ob_get_contents();
				ob_end_clean();
			}
			
			$blocks = $tmp_blocks;
		';
	
		return $lsCode;
	}

	// {{chunk [chunk_name]}} tag
	protected function EncoderBeginChunk($poMatched)
	{
		return "<?function chunk_". trim($poMatched[1]). '($parameters){?>';
	}
	
	// {{endchunk}} tag
	protected function EncoderEndChunk($poMatched)
	{
		return "<?}?>";
	}
	// {{block [block_name]}} tag
	protected function EncoderBeginBlock($poMatched)
	{
		$lsShort = trim($poMatched[1]);
		$lsName = "block_" . $lsShort . md5( time( 'YmdHis' ) );
		return '<? $blocks["' . $lsShort . '"] = "' . $lsName . '";  function '. $lsName . '($parameters){?>';
	}
	
	// {{endblock}} tag
	protected function EncoderEndBlock($poMatched)
	{
		return '<?}?>';
	}
	// {{ST }} tag - stencil insertion
	// poMatched semantics: { [chunk_name] [param1_name] [param1_value] ... [paramN_name] [paramN_value] }
	protected function EncoderStencilInsert($poMatched)
	{
		$lsCode = "";
		
		if (count($poMatched) > 1)
		{
			$lsCode = "<?=Templates::Code(Templates::Stencil(\"" . trim($poMatched[1]) . "\", array( ";
			
			$laParams = "";
			
			if (count($poMatched) > 2)
				if ($poMatched[2] !== "")
					$laParams = trim(preg_replace('/"\s*=/xi', '"=>', $poMatched[2]));
					
			$lsCode .= $laParams . "))); ?>";
		}
		
		return $lsCode;
	}	
	// {{C }} tag - chunk insertion
	// poMatched semantics: { [chunk_name] [param1_name] [param1_value] ... [paramN_name] [paramN_value] }
	protected function EncoderChunkInsert($poMatched)
	{
		$lsCode = "";
		
		if (count($poMatched) > 1)
		{
			$lsCode = "<? chunk_" . trim($poMatched[1]) . " ( array(";
			
			$lsParams = "";
			
			if (count($poMatched) > 2)
				if ($poMatched[2] !== "")
					$lsParams = preg_replace('/"\s*=/xi', '"=>', $poMatched[2]);
				
			$lsCode .= $lsParams . ")); ?>";
		}
		
		return $lsCode;
	}
	// {{M }} tag - macro insertion
	// poMatched semantics: { [template_name] [param1_name] [param1_value] ... [paramN_name] [paramN_value] }
	protected function EncoderMacroInsert($poMatched)
	{
		$lsCode = "";
		
		if (count($poMatched) > 1)
		{
			$lsCode = "<?=Templates::Code(Templates::Macro(\"" . trim($poMatched[1]) . "\", array(";
			
			
			$loParms = array();
			
			if (count($poMatched) > 2)
				if ($poMatched[2] !== "")
					$lsParams = trim(preg_replace('/"\s*=/xi', '"=>', $poMatched[2]));


			$lsCode .= $lsParams . "))); ?>";
		}
		
		return $lsCode;
	}
	// %%...%% tag - field mapping
	// poMatched semantics: { [field] [extensions] }
	protected function EncoderValueSubstitute($poMatched)
	{
		$lsCode = "";
		
		if (count($poMatched) > 1)
		{
			if ($this->IsFieldSet( trim($poMatched[1]) ))
				$lsCode = '$parameters["' . trim($poMatched[1]) . '"]';
			else		
				$lsCode = '$'. trim($poMatched[1]);
			
			if (count($poMatched) > 2)
				$lsCode .= $poMatched[2];
		}

		return $lsCode;
	}
	// @@...@@ - field substitute
	protected function EncoderFieldSubstitute($poMatched)
	{
		$lsCode = "";
		
		if (count($poMatched) > 1)
		{
			$lsCode = '$parameters["' . trim($poMatched[1]) . '"]';
			
			if (count($poMatched) > 2)
				$lsCode .= $poMatched[2];
		}
		
		return $lsCode;
	}
	// {{else}} tag
	protected function EncoderElse($poMatched)
	{
		return '<?}else{?>';
	}
	// {{foreach}} tag
	// poMatched semantics: { [what] [where] }
	protected function EncoderForeach($poMatched)
	{
		$lsCode = "";
		
		if (count($poMatched) > 2)
			$lsCode = '<?foreach ('.$poMatched[2].' as '.$poMatched[1].'){?>';
		
		return $lsCode;
	}
	// {{result <value>}} tag
	protected function EncoderResultOutput($poMatched)
	{
		$lsCode = "";
		
		if (count($poMatched) > 2)
			$lsCode = '<? $results["'.$poMatched[1].'"] = '.$poMatched[2].'; ?>';
		
		return $lsCode;
	}
	// {{foreach as}} tag
	// poMatched semantics: { [what] [where] }
	protected function EncoderForeachAsKV($poMatched)
	{
		$lsCode = "";
		
		if (count($poMatched) > 2)
			$lsCode = '<?foreach ('.$poMatched[3].' as '.$poMatched[2].'=>'.$poMatched[1].'){?>';
		
		return $lsCode;
	}
	// {{if ...}} tag
	// poMatched semantics: { [what] }
	protected function EncoderIf($poMatched)
	{
		$lsCode = "";
		
		if (count($poMatched) > 1)
			$lsCode = '<?if ('.$poMatched[1].'){?>';
		
		return $lsCode;
	}
	// {{i18n ...}} tag
	// poMatched semantics: { [what] }
	protected function EncoderI18n($poMatched)
	{
		$lsCode = "";
		
		if (count($poMatched) > 1)
			$lsCode = 'I18n::Get("'.addslashes($poMatched[1]).'")';
		
		return $lsCode;
	}
	// {{while ...}} tag
	// poMatched semantics: { [what] }
	protected function EncoderWhile($poMatched)
	{
		$lsCode = "";
		
		if (count($poMatched) > 1)
			$lsCode = '<?while ('.$poMatched[1].'){?>';
		
		return $lsCode;
	}
	// {{endfor}} tag
	protected function EncoderEndFor($poMatched)
	{
		return '<?}?>';
	}
	
	// {{endif}} tag
	protected function EncoderEndIf($poMatched)
	{
		return "<?}?>";
	}

	// {{endwhile}} tag
	protected function EncoderEndWhile($poMatched)
	{
		return "<?}?>";
	}

	// {{...}} tag - final output
	// poMatched semantics: { [contents] }
	protected function EncoderOutput($poMatched)
	{
		$lsCode = "";
		
		if (count($poMatched) > 1)
			$lsCode = '<?=htmlspecialchars(' . trim($poMatched[1]) . ');?>';
		
		return $lsCode;
	}

	// {{{...}}} tag - final raw output
	// poMatched semantics: { [contents] }
	protected function EncoderDirectOutput($poMatched)
	{
		$lsCode = "";
		
		if (count($poMatched) > 1)
			$lsCode = '<?='. trim($poMatched[1]) . ';?>';
		
		return $lsCode;
	}

	// %/% and @/@ tag - special symbols quotation
	// poMatched semantics: { [contents] }
	protected function EncoderQuote($poMatched)
	{
		if (count($poMatched) > 1)
			return str_replace( '\\', '', trim($poMatched[1]) );
		
		return "";
	}
}

class TemplateResult extends PropertyObject
{
	protected $_sCode = "";
	protected $_aResults = array();
	protected $_aBlocks = array();
	protected $_sSource = "";

	protected function set_Code ($poValue)		{ $this->_sCode= $poValue; }
	protected function get_Code ()		{ return $this->_sCode; }
	
	protected function set_Results ($poValue)		{ $this->_aResults= $poValue; }
	protected function get_Results ()		{ return $this->_aResults; }
	
	protected function set_Blocks ($poValue)		{ $this->_aBlocks= $poValue; }
	protected function get_Blocks ()		{ return $this->_aBlocks; }
	
	protected function set_Source ($poValue)		{ $this->_sSource= $poValue; }
	protected function get_Source ()		{ return $this->_sSource; }
		
	public function TemplateResult($psCode, $paResults, $paBlocks, $psSource = "")
	{
		$this->Code = $psCode;
		$this->Results = $paResults;
		$this->Blocks = $paBlocks;
		$this->Source = $psSource;
	}
}

class Templates
{
	protected static $_nLevel = 0;
	protected static $_sCurrent = "";
	
	public static function	Down() 
	{ 
		Templates::$_nLevel ++; 
	}
	public static function	Up() 
	{ 
		if ( Templates::$_nLevel > 0 )
			Templates::$_nLevel --;
	}
	public static function AlreadyInTemplate()
	{
		return ( Templates::$_nLevel>0 );
	}
	public static function Current()
	{
		return Templates::$_sCurrent;
	}
	private static function SetCurrent($psValue)
	{
		Templates::$_sCurrent = $psValue;
	}
	public static function Output($psValue)
	{
		Net::Output( $psValue );
	}
	private	static function SpawnTemplate($psClassName = "", $poParams = array())
	{
		if ($psClassName == "")
			$psClassName = "CTemplate";
		
		$loTemplate = new $psClassName();
		$loTemplate->SetArray($poParams);
		
		return $loTemplate;
	}
	public static function ResolveName($psFileName)
	{
		return Config::$Templates["tplpath"]."/".$psFileName;
	}

	public static function	TextTemplate($psText, $poParams = array(), $psClassName="")
	{
		Templates::SetCurrent("");
		$loTemplate = Templates::SpawnTemplate($psClassName, $poParams);
		
		return $loTemplate->ProcessFromText($psText);
	}

	public static function	Template($psFileName, $poParams = array(), $psClassName="")
	{
		Templates::SetCurrent($psFileName);
		$loTemplate = Templates::SpawnTemplate($psClassName, $poParams);
		
		return $loTemplate->Process($psFileName);
	}
	
	public static function	Stencil($psName, $laArgs = array(), $psClassName="")
	{
		$loStencil = Stencils::GetByName($psName);

		if ($loStencil == null)
			return array();
			
		return $loStencil->Render($laArgs);
	}
	
	public static function	Macro($psFileName, $poParams=array())
	{
		$lsFileName = $psFileName . ".tpl.php"; 
		$loTemplate = Templates::SpawnTemplate("CTemplate", $poParams);

		// Отладочная настройка для забивания на кэш. Надо, конечно, сделать инвалидацию кэша по изменению файла.	
		$loCached = null;
		if ( !Config::$System["macro.cache.always.miss"] )
			$loCached = Cache::Get( $lsFileName );

		if ($loCached == null)
		{
			$loCached = $loTemplate->Preprocess( $lsFileName );
			// Закэшируем макросы на 10 суток
			Cache::Set( $lsFileName, $loCached, 864000 );
		}

		Templates::SetCurrent( $lsFileName );
		return $loTemplate->ProcessFromCache( $loCached );
	}
	
	public static function Code( $paFrom ) { return $paFrom->Code; }
	public static function Results( $paFrom ) { return $paFrom->Results; }
	public static function Blocks( $paFrom ) { return $paFrom->Blocks; }
	public static function Source( $paFrom ) { return $paFrom->Source; }
	public static function Timestamp( $paFrom ) { return $paFrom->Timestamp; }
}
?>
