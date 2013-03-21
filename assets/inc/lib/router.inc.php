<?
class Router
{
	private static $_oRoutes = array();
	private static $_oFailsafeRoute = array();
	private static $_sCurrentRoutingString = "";
	private static $_sEffectivePath = "";
	
	public static function AddRoute( $psTemplate = "", $psCallPath = "", $pbNeedAuth = false)
	{
		Router::$_oRoutes[] = array( 
			"path" => $psTemplate,
			"method" => $psCallPath,
			"need_auth" => ($pbNeedAuth == true)
		);
	}
	
	public static function PushFailsafeRoute( $psCallPath )
	{	
		array_unshift(Router::$_oFailsafeRoute, $psCallPath);
	}

	public static function PopFailsafeRoute( $psCallPath )
	{	
		array_shift( Router::$_oFailsafeRoute );
	}
	
	protected static function MakePathProper( $psPath ) { return str_replace("//", "/", "/" . $psPath . "/"); }

	protected static function FindRoute( $psPath )
	{
		$lnMaxLen = -1;
		$loMaxItem = null;
		$lsEffectivePath = "";
		$lbIsLogged = Users::LoggedAs() != null;

		// Найдём ближайший из маршрутов	
		foreach(Router::$_oRoutes as $loValue)
		{
			$lsKey = $loValue["path"];

			if ( (@strstr($psPath, $lsKey) || ($psPath == $lsKey)) && ( ($loValue["need_auth"] && $lbIsLogged) || (!$loValue["need_auth"]) ) )
			{
				if ($lnMaxLen <= strlen($lsKey) )
				{				
					$lnMaxLen = strlen($lsKey);
					$loMaxItem = $loValue;
					$lsEffectivePath = $lsKey;
				}
			}
		}
		
		return  $loMaxItem;
	}
	
	protected static function InvokeFailsafeStack( $psPath )
	{
		Router::SetRoutingString( $psPath );
		Router::SetEffectivePath( "" );

		$lsArgs = Router::BuildStringArgumentsFromPath( $psPath );

		foreach (Router::$_oFailsafeRoute as $lsRoute)
			if ($lsRoute != "")
			{
				$lbResult = false;
				eval( '$lbResult = ' . $lsRoute . '(' . $lsArgs . ');' );
				if ($lbResult)
					return true;
			}
		
		return false;
	}
	
	public static function BuildStringArgumentsFromPath( $psPath )
	{
		$loArgs = explode("/", $psPath);
		array_pop($loArgs);
		
		for ($i=0; $i<count($loArgs); $i++)
			$loArgs[$i] = '"'.$loArgs[$i].'"';
	
		$lsArgs = implode(",", $loArgs);
		if ($lsArgs=='""') $lsArgs = "";
		
		return $lsArgs;
	}

	public static function Route( $psPath="" )
	{
		// Откорректируем строку маршрутизации (slashes, etc)
		$lsPath = Router::MakePathProper( $psPath );
		
		// Если выставлена блокировка сайта — никакой вам маршрутизации, 
		// пока не докажете, что можете контролировать силу
		if (!Users::AllowSiteEntering())
			return Router::InvokeFailsafeStack( $lsPath );
		
		// Поиск маршрута
		$loRoute = Router::FindRoute( $lsPath );
		
		// Не нашли — бида, бида
		if ($loRoute == null)		
			return Router::InvokeFailsafeStack( $lsPath );
			
		// Подготовим окружение
		Router::SetRoutingString( $lsPath );
		Router::SetEffectivePath( $loRoute["path"] );

		// Достанем из пути параметры для передачи в маршрут.
		// Параметры достаём, вырезая из НАЧАЛА строки маршрутизатора строку маршрута
		$lsUnique = "<<~BADASS~>>";
		$lsArgs = Router::BuildStringArgumentsFromPath( str_replace( $lsUnique . $loRoute["path"], "", $lsUnique . $lsPath  ) );
		
		// Рендеринг!
		$lbResult = false;
		$lsToCall = '$lbResult = ' . $loRoute["method"] . "(" . $lsArgs . ");";
		eval($lsToCall);

		// Не получилось? Бидааа...
		if (!$lbResult)
			return Router::InvokeFailsafeStack( $lsPath );

		return true;
	}
	
	public static function SetRoutingString($psPath)
	{
		Router::$_sCurrentRoutingString = $psPath;
	}
	
	public static function GetRoutingString()
	{
		return Router::$_sCurrentRoutingString;
	}

	public static function SetEffectivePath($psPath)
	{
		Router::$_sEffectivePath = $psPath;
	}

	public static function GetEffectivePath()
	{
		return Router::$_sEffectivePath;
	}
}
?>
