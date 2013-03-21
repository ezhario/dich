<?
class Users
{
	public static function GetEmpty()                                   { return DB::EntityDescriptor("dich_users")->EmptyEntity(); }
	public static function Get( $pnId ) 				    { return DB::EntityDescriptor("dich_users")->EmptyEntity()->Load($pnId); }
	public static function All( $psSelector = null, $psOrder = null)    { return DB::EntityDescriptor("dich_users")->Entities( $psSelector, ($psOrder == null) ? (QB::Field( "login" )) : $psOrder ); }

	protected static $_oLoggedUser = null;
	protected static $_sSessionId = null;
	protected static $_oSiteLockProperty = null;
	protected static $_oSiteLockPasswordProperty = null;

	public static function SessionId()
	{
		return Users::$_sSessionId;
	}
	
	public static function LoggedAs()
	{
		if (Users::$_oLoggedUser == null)
		{
			if ( isset($_SESSION["logged_user"]) )
				Users::$_oLoggedUser = Users::Get( $_SESSION["logged_user"] );
		}
	
		return Users::$_oLoggedUser;
	}

	public static function Login( $psLogin, $psPassword)
	{
		if ( Users::LoggedAs() != null ) return true;

		$laResult = DB::EntityDescriptor("dich_users")->Entities( QB::EAnd( QB::E("login", "=", $psLogin), QB::E("password_hash", "=", md5($psPassword)) ) );				
		
		if ($laResult == null) return false;
		if (count($laResult) == 0) return false;

		$laUserIds = array_keys( $laResult );		
		
		$_SESSION["logged_user"] = $laUserIds[0];
		$_SESSION["session_id"] = session_id();

		return true;
	}
	
	public static function Logout()
	{
		$lbUnlocked = Users::AllowSiteEntering();
		
		session_destroy();
		session_start();
		$_SESSION = array();
		
		if ($lbUnlocked)
			$_SESSION["site_unlocked"] = "1";
	}
	
	public static function Init( $psSid = null)
	{
		$lnTTL = Config::$System["cookielifetime"];
		
		if ($psSid != null)
			session_id( $psSid );
		
		session_start();
		
		Users::$_sSessionId = session_id();
		
		Users::$_oSiteLockProperty = Settings::GetByName("site_lock");
		Users::$_oSiteLockPasswordProperty = Settings::GetByName("site_lock_password");
 	}
 	
 	public static function AllowSiteEntering()
 	{
 		if ($_SESSION["site_unlocked"] == "1")
 			return true;

 		if ( (Users::$_oSiteLockProperty == null) || (Users::$_oSiteLockPasswordProperty == null))
 			return true;
 		
 		if ( Users::$_oSiteLockProperty->Value != "1")
 			return true;
 			
 		return false;
 	}
 	
 	public static function TryToUnlockSiteEntering($psPassword)
 	{
 		if ( Users::$_oSiteLockPasswordProperty != null )
 			if ( (Users::$_oSiteLockPasswordProperty->Value == $psPassword) )
				$_SESSION["site_unlocked"] = "1"; 		
 	}
}

class User extends Entity
{
	protected function set_Login( $psValue )	{ $this->Set("login", trim($psValue)); }
	protected function get_Login( )				{ return $this->Get("login"); }

	protected function set_First( $psValue )	{ $this->Set("first", trim($psValue)); }
	protected function get_First( )				{ return $this->Get("first"); }
	
	protected function set_Second ($poValue)	{ $this->Set("second", trim($poValue)); }
	protected function get_Second ( )	{ return $this->Get("second"); }
	
	protected function set_Last ($poValue)		{ $this->Set("last", trim($poValue)); }
	protected function get_Last ( )		{ return $this->Get("last"); }
	
	protected function get_Name ( )		{ return implode(" ", array( $this->Last, $this->First, $this->Second )); }
	
	protected function set_Phone ($poValue)		{ $this->Set("phone", trim($poValue)); }
	protected function get_Phone ( )		{ return $this->Get("phone"); }

	protected function set_AccessId ($poValue)	{ $this->Set("access_id", trim($poValue)); }
	protected function get_AccessId ( )			{ return $this->Get("access_id"); }
	
	protected function set_Password ($poValue)		{ $this->Set("password_hash", md5(trim($poValue))); }

	protected function set_Description( $psValue )	{ $this->Set("description", trim($psValue)); }
	protected function get_Description( )			{ return $this->Get("description"); }
	
	protected function set_Session ($poValue)		{ $this->Set("session", serialize($poValue)); }
	protected function get_Session ( )		{ $loArray = $this->Get(unserialize("session")); return is_array($loArray) ? $loArray : array(); }

	public function GatherFromPublicDomain($paValue)
	{
		$this->Login		=	$paValue["login"];
		$this->First		=	$paValue["first"];
		$this->Second		=	$paValue["second"];
		$this->Last			=	$paValue["last"];
		$this->Phone		=	$paValue["phone"];

		// Удостоверимся в том, что пароль просто так не поменять		
		if ( ($paValue["password"] === $paValue["password2"]) && ($paValue["password"] !== "") )
			$this->Password		=	$paValue["password"];

		$this->Description	=	$paValue["description"];
		$this->AccessId		=	$paValue["access_id"];
						
		return Entity::GatherFromPublicDomain( $paValue );
	}
	
	public function Del()
	{
		return Entity::Del();
	}	
}

$descriptor = new EntityDescriptor( "dich_users", "User" );
$descriptor
	-> DeclareField("first", "text")
	-> DeclareField("second", "text")
	-> DeclareField("last", "text")
	-> DeclareField("phone", "text")
	-> DeclareField("login", "text")
	-> DeclareField("password_hash", "text")
	-> DeclareField("session", "text")
	-> DeclareField("access_id", "bigint")
	-> DeclareField("description", "text");
	
DB::RegisterEntityDescriptor( $descriptor);

// Для работы необходимо в нужном месте вызвать Users::Init();
?>
