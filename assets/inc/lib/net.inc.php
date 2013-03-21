<?

class Net
{
	protected static $_aPOST;
	protected static $_aGET;

	public static function Init()
	{
		Net::$_aPOST = array();
		Net::$_aGET = array();
		
		foreach ($_GET as $k=>$v) Net::$_aGET[$k] = stripslashes($v);
		foreach ($_POST as $k=>$v) Net::$_aPOST[$k] = stripslashes($v);
	}

	public static function Redirect( $psWhere )
	{
		$lsWhere = "";
		if (is_string($psWhere))
			$lsWhere = $psWhere;
		else
		if (is_object($psWhere))
			$lsWhere = $psWhere->Get();

		header("Location: ".$lsWhere."\n\n");
		die;		
	}
	
	public static function ForceInvalidateCaching()
	{
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	}
	
	public static function Report404()
	{
		header("HTTP/1.0 404 Not Found");
		die;
	}
	
	public static function GetResult( $psField = null, $psDefaultValue = null )
	{
		if ($psField == null)
			return Net::$_aGET;

		if (isset(Net::$_aGET[$psField]))
			return Net::$_aGET[$psField];
			
		return $psDefaultValue;		
	}
	
	public static function PostResult( $psField = null, $psDefaultValue = null )
	{
		if ($psField == null)
			return Net::$_aPOST;

		if (isset(Net::$_aPOST[$psField]))
			return Net::$_aPOST[$psField];
			
		return $psDefaultValue;		
	}

	public static function UploadedFiles( $psName = null )
	{
		if ($psName == null)
			return $_FILES;

		if (isset($_FILES[$psName]))
			return $_FILES[$psName];
			
		return null;		
	}
	
	public static function URL( $psUrl = null )
	{
		return new URL($psUrl);	
	}
	
	public static function Output( $psValue )
	{
		print($psValue);
	}
}

class JSON
{
	public static function Encode( $poObject )
	{
		return json_encode($poObject);
	}
	
	public static function Decode( $psObject )
	{
		return json_decode($psObject);
	}	
}

class URL
{
	protected $_aElements = array();
	protected $_sArguments = "";
	protected $_sFragment = "";
	
	public function URL( $psUrl = null )
	{
		$this->Set( Router::GetRoutingString() );
		$this->Set( $psUrl );
	}
	
	public function Set( $psNewUrl )
	{
		if ($psNewUrl == null) return $this;
	
		$laTmpUrl = $this->_aElements;
		
		$psNewUrl = str_replace("//", "/", $psNewUrl);
		$paUrlParts = parse_url($psNewUrl);
		
		$psNewUrl = $paUrlParts["path"];
		$this->_sArguments = isset($paUrlParts["query"]) ? $paUrlParts["query"] : "";
		$this->_sFragment = isset($paUrlParts["fragment"]) ? $paUrlParts["fragment"] : "";
				
		$lbInitialized = false;
		
		foreach (explode("/", $psNewUrl) as $v)
		{
			if ($v == "") { if (!$lbInitialized) { $laTmpUrl = array(); } }
			else if ($v == ".") {}
			else if ($v == "..") { array_pop($laTmpUrl); }
			else array_push($laTmpUrl, $v);
		
			$lbInitialized = true;
		}

		$this->_aElements = $laTmpUrl;
		
		return $this;
	}
	
	public function Get()
	{
		return 	"/" . implode("/", $this->_aElements) . 
				(count($this->_aElements) > 0 ? "/" : "") . 
				($this->_sArguments != "" ? ("?" . $this->_sArguments) : "" ) .
				($this->_sFragment != "" ? ("#" . $this->_sFragment) : "" );
	}
	
	public function GetElements()
	{
	    return $this->_aElements;
	}

	// Сравнение двух УРЛов (например - для навигации)
	// 	
	// 0 - УРЛы вообще не пересекаются
	// 1 - Текущий УРЛ принадлежит УРЛу-аргументу
	// 2 - Текущий УРЛ совпадает с аргументом
	// 3 - Текущий УРЛ поглощает УРЛ-аргумент
	public function Compare( URL $poCompareWith )
	{
		$i=0;		
		$len = count($poCompareWith->_aElements);
				
		foreach ($this->_aElements as $k)
		{
			if ($i >= $len) return 1;
			if ($poCompareWith->_aElements[$i] != $k) return 0;
			++$i;
		}
		
		if (count($this->_aElements) != $len) return 3;
		
		return 2;
	}
}

Net::Init();
?>
