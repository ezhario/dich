<?
include_once("i18n.inc.php");		// Интернационализация

class Config
{
	public static function Init()
	{
		// Стандартные настройки
		Config::$System["documentroot"] = rtrim( $_SERVER["DOCUMENT_ROOT"], "/" );
		
		Config::$System["assetspath"] = Config::$System["documentroot"]."/assets";
		Config::$System["cachepath"] = Config::$System["assetspath"]."/cache";
		Config::$System["basepath"] = "/";
		Config::$System["i18n"] = "ru";
		Config::$System["locale"] = "ru_RU";
		
		// Аплоады, кэш, куки
		Config::$System["uploadspath"] = Config::$System["documentroot"]."/uploads";
		Config::$System["cookielifetime"] = 86400;
		Config::$System["cachelifetime"] = 86400;
		Config::$System["macro.cache.always.miss"] = false;

		// Шаблоны
		Config::$Templates["tplpath"] = Config::$System["assetspath"]."/tpl";
		
		// Настройки базы данных		
		Config::$DB["host"] = "";
		Config::$DB["port"] = "3306";
		Config::$DB["user"] = "";
		Config::$DB["database"] = "dich";
		Config::$DB["password"] = "";
		Config::$DB["charset"] = "utf8";
	
		// Заголовок редактора
		Config::$Site["title"] = "Управление сайтом";

		// Подключим внешнюю конфигурацию
		$psSource = Config::$System["documentroot"] . "/assets/inc/configuration.inc.php";

	    I18n::Init();

	    include ($psSource);

		setlocale(LC_ALL, Config::$System["locale"] . '.UTF8');
	    if (Config::$System["i18n"] != "en")
		    I18n::Init( Config::$System["i18n"] );
	}

	public static $DB = array();
	public static $System = array();
	public static $Templates = array();	
	public static $Site = array();
	
	public static function InstallScriptExists()
	{
		return @file_exists(Config::$System["assetspath"] . "/INSTALL.php");	
	}
}

Config::Init();
?>
