<?

class CacheItem extends PropertyObject
{
	protected $_sObject = "";
	protected $_sTimestamp = 0;
	protected $_sTTL = 0;
	protected $_sHash = "";


	protected function set_Object ($poValue)	{ $this->_sObject= $poValue; }
	protected function get_Object ()			{ return $this->IsValid ? $this->_sObject : null; }
	
	protected function set_Timestamp ($poValue)	{ $this->_sTimestamp= $poValue; }
	protected function get_Timestamp ()			{ return $this->_sTimestamp; }
	
	protected function set_TTL ($poValue)		{ $this->_sTTL= $poValue; }
	protected function get_TTL ()				{ return $this->_sTTL; }
	
	protected function get_Hash ()				{ return $this->_sHash; }
	protected function get_FileName()			{ return Cache::HashToFileName( $this->_sHash ); }
	
	protected function get_IsValid()			{ return ($this->_sTimestamp+$this->_sTTL) > time(); }


	public function CacheItem($psHash)
	{
		$this->_sTTL = Config::$System["cachelifetime"];
		$this->_sHash = $psHash;
	}
	
	public static function Load( $psHash )
	{
		$loObject = unserialize( file_get_contents( Cache::HashToFileName( $psHash ) ) );
		return ($loObject == false) ? new CacheItem( $psHash ) : $loObject;
	}
	
	public function Save()
	{
		if ( $poHandle = fopen( $this->FileName, "x+" ) )
		{
			$this->Timestamp = time();
			
			fwrite( $poHandle, serialize($this) );
			fclose( $poHandle );
			
			return true;
		}
		
		return false;
	}
}

class Cache
{
	private static $_aCache = array();
	private static $_aHashCache = array();

	public static function Init()
	{
	}
	
	private static function Hash( $psName )
	{
		// Проверим, есть ли в словаре хэшей такой хэш
		$lsValue = Cache::$_aHashCache[$psName];
		
		if ( isset( $lsValue ) )
			return $lsValue;
		
		// Иначе сгенерируем новый и поместим его в словарь
		$lsResult = md5( $psName );
		Cache::$_aHashCache[$psName] = $lsResult;
			
		return $lsResult;
	}
	
	public static function HashToFileName( $psHash )
	{
		return Config::$System["cachepath"] . "/" . $psHash;
	}
	
	public static function Get( $psName )
	{
		// Получим хэш объекта
		$lsHash = Cache::Hash( $psName );
		
		// Попробуем найти его в локальном кэше
		$loCacheItem = Cache::$_aCache[$lsHash];
		
		if ( isset( $loCacheItem ) )
			return $loCacheItem->Object;
			
		// Попробуем достать его с диска
		$loCacheItem = CacheItem::Load( $lsHash );
		
		Cache::$_aCache[$lsHash] = $loCacheItem;
		
		return $loCacheItem->Object;
	}
	
	public static function Set( $psName, $poContents, $pnTTLInSeconds = 0 )
	{
		// Проверим время жизни записей
		$lnTTLInSeconds = $pnTTLInSeconds;
		if ($lnTTLInSeconds == 0)
			$lnTTLInSeconds = Config::$System["cachelifetime"];
	
		// Получим хэш объекта
		$lsHash = Cache::Hash( $psName );
		
		// Попробуем найти его в локальном кэше
		$loCacheItem = Cache::$_aCache[$lsHash];

		// Попробуем достать его с диска
		if ( !isset( $loCacheItem ) )
			$loCacheItem = CacheItem::Load( $lsHash );

		$loCacheItem->Object = $poContents;
		$loCacheItem->TTL = $lnTTLInSeconds;

		Cache::$_aCache[$lsHash] = $loCacheItem;	
		
		return $loCacheItem->Save();
	}
}

Cache::Init();
?>
