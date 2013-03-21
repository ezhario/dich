<?
class Stencils
{
	/* Методы получения шаблонов */
	public static function GetEmpty()									{ return DB::EntityDescriptor("dich_stencils")->EmptyEntity(); }
	public static function Get( $pnId ) 								{ return DB::EntityDescriptor("dich_stencils")->EmptyEntity()->Load($pnId); }
	public static function GetByName( $psName )							
	{ 
		$laEntities = DB::EntityDescriptor("dich_stencils")->Entities( 
			QB::E( "name", "=", $psName)
		);
		
		return (count($laEntities) > 0) ? array_shift($laEntities) : null;
	}
	public static function All( $psSelector = null, $psOrder = null)	
	{ 
		return DB::EntityDescriptor("dich_stencils")->Entities( $psSelector, 
			($psOrder == null) ? 
			array(QB::Field("is_top_level","DESC"), QB::Field( "name" )) : $psOrder 
		); 
	}
	public static function AllTopLevel()								{ return Stencils::All( QB::E( "is_top_level", "=", "1" ) ); }
}

/* Модель шаблона */
class Stencil extends Entity
{
	/* Геттеры и сеттеры для сущности */
	protected function set_Title( $psValue )		{ $this->Set("title", $psValue); }
	protected function get_Title( )					{ return $this->Get("title"); }
	
	protected function set_Name( $psValue )			{ $this->Set("name", $psValue); }
	protected function get_Name( )					{ return $this->Get("name"); }
	
	protected function set_Description( $psValue )	{ $this->Set("description", $psValue); }
	protected function get_Description( )			{ return $this->Get("description"); }
	
	protected function set_Contents( $psValue )		{ $this->Set("contents", $psValue); }
	protected function get_Contents( )				{ return $this->Get("contents"); }

	protected function set_RefCount( $psValue )		{ return $this->Set("refcount", $psValue); }
	protected function get_RefCount( )				{ return $this->Get("refcount"); }

	protected function set_IsTopLevel( $psValue )	{ if ($this->RefCount == 0) $this->Set("is_top_level", $psValue==true ? "1" : "0" ); }
	protected function get_IsTopLevel( )			{ return $this->Get("is_top_level") != "0"; }

	public function GatherFromPublicDomain($paValue)
	{
    	$this->Title 		= $paValue["title"];
    	$this->Description 	= $paValue["description"];
    	$this->Contents		= $paValue["contents"];
    	$this->Name			= $paValue["name"];
		$this->IsTopLevel	= $paValue["is_top_level"] == "1";
		
		return Entity::GatherFromPublicDomain( $paValue );
	}

	public function Ref()
	{
		EntityReferencing::Ref( $this );
	}
	
	public function Unref()
	{
		EntityReferencing::Unref( $this );
	}
	
	public function Del()
	{
		if ($this->RefCount == 0)
			if (Entity::Del())
				return true;
		
		return false;
	}	
	
	public function Render( $paArgs = array() )
	{
		$laArgs = is_array($paArgs) ? $paArgs : array();
		return Templates::TextTemplate( '?>' . $this->Contents . '<?', $laArgs );
	}
}

$descriptor = new EntityDescriptor( "dich_stencils", "Stencil" );
$descriptor
	-> DeclareField("title", "text")
	-> DeclareField("name", "text")
	-> DeclareField("description", "text")
	-> DeclareField("contents", "longtext")
	-> DeclareField("is_top_level", "boolean default 0")
	-> DeclareField("refcount", "bigint default '0'");
	
DB::RegisterEntityDescriptor( $descriptor);
?>
