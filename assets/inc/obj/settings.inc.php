<?
class Settings
{
	/* Расширенные методы получения настроек */
	public static function GetEmptySystem()								
	{ 
		$loEntity = Settings::GetEmpty();
		
		if ($loEntity != null)
		{
			$loEntity->IsSystem = true;
		}
		
		return $loEntity; 
	}
	public static function GetEmptyContentBound( $pnContentId )
	{ 
		$loEntity = Settings::GetEmpty();
		
		if ($loEntity != null)
		{
			$loEntity->ContentId = $pnContentId;
		}
		
		return $loEntity; 
	}
	public static function GetEmptyFieldBound( $pnDatasetFieldId )								
	{ 
		$loEntity = Settings::GetEmpty();
		
		if ($loEntity != null)
		{
			$loEntity->DatasetFieldId = $pnDatasetFieldId;
		}
		
		return $loEntity; 
	}

	/* Методы получения настроек */
	public static function GetEmpty()									{ return DB::EntityDescriptor("dich_settings")->EmptyEntity(); }
	public static function Get( $pnId ) 								{ return DB::EntityDescriptor("dich_settings")->EmptyEntity()->Load($pnId); }
	public static function GetByName( $psName )							
	{ 
		$laEntities = DB::EntityDescriptor("dich_settings")->Entities( 
			QB::E( "name", "=", $psName)
		);
		
		return (count($laEntities) > 0) ? array_shift($laEntities) : null;
	}
	public static function All( $psSelector = null, $psOrder = null)	
	{ 
		return DB::EntityDescriptor("dich_settings")->Entities( $psSelector, 
			($psOrder == null) ? 
			array(QB::Field( "section" )) : $psOrder 
		); 
	}
	public static function Types()
	{
		return SettingsEntryType::All();
	}
}

class SettingsEntryType
{
	private static $_aCachedAll = null;

	public static function All()
	{
		if (SettingsEntryType::$_aCachedAll == null)
			SettingsEntryType::$_aCachedAll = array(
				SettingsEntryType::Text() => I18n::Get("system.settings_types.text"),
				SettingsEntryType::Number() => I18n::Get("system.settings_types.number"),
				SettingsEntryType::Boolean() => I18n::Get("system.settings_types.boolean"),
				SettingsEntryType::Password() => I18n::Get("system.settings_types.password"),
				SettingsEntryType::BigText() => I18n::Get("system.settings_types.big_text"),
			);
		return SettingsEntryType::$_aCachedAll;
	}
	
	public static function Text() { return "0"; }
	public static function Number() { return "1"; }
	public static function Boolean() { return "2"; }
	public static function Password() { return "3"; }
	public static function BigText() { return "4"; }
}

/* Модель шаблона */
class SettingsEntry extends Entity
{
	/* Геттеры и сеттеры для сущности */
	protected function set_Title( $psValue )		{ $this->Set("title", $psValue); }
	protected function get_Title( )					{ return $this->Get("title"); }
	
	protected function set_Name( $psValue )			{ $this->Set("name", $psValue); }
	protected function get_Name( )					{ return $this->Get("name"); }
	
	protected function set_Description( $psValue )	{ $this->Set("description", $psValue); }
	protected function get_Description( )			{ return $this->Get("description"); }
	
	protected function set_Section( $psValue )		{ $this->Set("section", $psValue); }
	protected function get_Section( )				{ return $this->Get("section"); }

	protected function set_ContentId( $psValue )		{ $this->Set("c_id", $psValue); }
	protected function get_ContentId( )					{ return $this->Get("c_id"); }

	protected function set_DatasetFieldId( $psValue )	{ $this->Set("df_id", $psValue); }
	protected function get_DatasetFieldId( )			{ return $this->Get("df_id"); }
	
	protected function set_IsSystem( $psValue )		{ $this->Set("is_system", $psValue); }
	protected function get_IsSystem( )				{ return $this->Get("is_system"); }

	protected function set_DataType( $psValue )		{ $this->Set("data_type", $psValue); }
	protected function get_DataType( )				{ return $this->Get("data_type"); }

	protected function set_MinValue( $psValue )		{ $this->Set("min_value", $psValue); }
	protected function get_MinValue( )				{ return $this->Get("min_value"); }

	protected function set_MaxValue( $psValue )		{ $this->Set("max_value", $psValue); }
	protected function get_MaxValue( )				{ return $this->Get("max_value"); }

	protected function set_Value( $psValue )		{ $this->Set("value", $psValue); }
	protected function get_Value( )					{ return $this->Get("value"); }
	
	protected function get_CanBeDeleted()
	{
		return $this->IsUserSettingsEntry;
	}

	protected function get_IsUserSettingsEntry()
	{
		return ( !$this->IsSystem ) && ( $this->ContentId == 0 ) && ( $this->DatasetFieldId == 0 );
	}

	public function GatherFromPublicDomain($paValue)
	{
    	$this->Title 		= $paValue["title"];
    	$this->Name			= $paValue["name"];
    	$this->Description 	= $paValue["description"];
    	$this->Section		= $paValue["section"];
    	
    	if ($this->IsUserSettingsEntry)
    	{
			$this->DataType	= $paValue["data_type"];
		}
		
		if (isset( $paValue["min_value"] ))
			$this->MinValue 	= $paValue["min_value"];
		if (isset( $paValue["max_value"] ))
			$this->MaxValue 	= $paValue["max_value"];
		if (isset( $paValue["value"] ))
			$this->Value 		= $paValue["value"];
		
		return Entity::GatherFromPublicDomain( $paValue );
	}

	public function Del()
	{
		if ($this->CanBeDeleted)
			if (Entity::Del())
				return true;
		
		return false;
	}	
}

$descriptor = new EntityDescriptor( "dich_settings", "SettingsEntry" );
$descriptor
	-> DeclareField("title", "text")
	-> DeclareField("name", "text")
	-> DeclareField("description", "text")
	-> DeclareField("section", "text")
	-> DeclareField("c_id", "bigint default '0'")
	-> DeclareField("df_id", "bigint default '0'")
	-> DeclareField("is_system", "boolean default 0")
	-> DeclareField("data_type", "bigint default '0'")
	-> DeclareField("min_value", "bigint default '0'")
	-> DeclareField("max_value", "bigint default '0'")
	-> DeclareField("value", "text");
	
DB::RegisterEntityDescriptor( $descriptor );
?>
