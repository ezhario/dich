<?
class I18n
{
	private static $_aLocaleData = array();

	public static function Init ( $psLocale = "en" )
	{
		include_once( "i18n/" . $psLocale . ".inc.php" );
	}
	
	public static function Set( $psKey, $psValue )	{ I18n::$_aLocaleData[ $psKey ] = $psValue; }
	public static function Get( $psKey )			{ return I18n::$_aLocaleData[ $psKey ];	}
	
	public static function UniversalTransliterateFilename( $psName )
	{
		// Метод стрёмный и медленный. Простите меня!
		$lsLowercaseName = mb_strtolower( $psName, "UTF-8" );
		$lsResult = strtr( $lsLowercaseName, I18n::Get( "lookup.transliterate" ) );
		$lsResult = preg_replace( '/[^a-zA-Z0-9_\\-\\.]/', "", $lsResult);
	
		return $lsResult;
	}
}
?>
