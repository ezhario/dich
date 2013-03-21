<?
/* Модель датасетов */
class Datasets
{
	/* Хранилище классов полей датасета */
	protected static $_aFieldClasses = array();
	public static function RegisterFieldClass( $psClassName, $psTitle )	{ Datasets::$_aFieldClasses[$psClassName] = $psTitle; }
	public static function GetFieldClasses()	{ return Datasets::$_aFieldClasses;	}

	/* Методы получения датасетов */
	public static function GetEmpty()									{ return DB::EntityDescriptor("dich_datasets")->EmptyEntity(); }
	public static function Get( $pnId ) 								{ return DB::EntityDescriptor("dich_datasets")->EmptyEntity()->Load($pnId); }
	public static function All( $psSelector = null, $psOrder = null)	
	{ 
		$lsIsBoundCheck = QB::E("bound", "=", "0");
		$lsSelector = $psSelector == null ? $lsIsBoundCheck : QB::EAnd($psSelector, $lsIsBoundCheck);
		return DB::EntityDescriptor("dich_datasets")->Entities( $lsSelector, ($psOrder == null) ? (QB::Field( "title" )) : $psOrder ); 
	}
	public static function Bound( $psSelector = null, $psOrder = null)	
	{ 
		$lsIsBoundCheck = QB::E("bound", "=", "1");
		$lsSelector = $psSelector == null ? $lsIsBoundCheck : QB::EAnd($psSelector, $lsIsBoundCheck);
		return DB::EntityDescriptor("dich_datasets")->Entities( $lsSelector, ($psOrder == null) ? (QB::Field( "title" )) : $psOrder ); 
	}
}

/* Модель датасета */
class Dataset extends Entity
{
	/* Геттеры и сеттеры для сущности */
	protected function set_Name( $psValue )			{ $this->Set("name", $psValue); }
	protected function get_Name( )					{ return $this->Get("name"); }
	protected function set_Title( $psValue )		{ $this->Set("title", $psValue); }
	protected function get_Title( )					{ return $this->Get("title"); }
	protected function set_Description( $psValue )	{ $this->Set("description", $psValue); }
	protected function get_Description( )			{ return $this->Get("description"); }
	protected function set_IsBound( $psValue )		{ $this->Set("bound", $psValue==true ? "1" : "0" ); }
	protected function get_IsBound( )				{ return $this->Get("bound") != "0"; }
	
	public function GatherFromPublicDomain($paValue)
	{
    	$this->Name 		= $paValue["name"];
    	$this->Title 		= $paValue["title"];
    	$this->Description	= $paValue["description"];
		
		return Entity::GatherFromPublicDomain( $paValue );
	}
	
	/* Методы получения полей */
	public function GetEmptyField()			
	{
		if ( !isset($this->Id) ) return null;
		
		$loEntity = DB::EntityDescriptor("dich_datasets_fields")->EmptyEntity();
		$loEntity->Set("d_id", $this->Id);
		
		return $loEntity;
	}
	public function GetField( $pnId ) 									{ return DB::EntityDescriptor("dich_datasets_fields")->EmptyEntity()->Load($pnId); }
	public function AllFields( $psSelector = null, $psOrder = null)	
	{
		$lsLocalSelector = QB::E("d_id", "=", $this->Id);
		$lsSelector = ($psSelector == null) ? $lsLocalSelector : QB::EAnd($psSelector, $lsLocalSelector);
		
		return DB::EntityDescriptor("dich_datasets_fields")->Entities( $lsSelector, ($psOrder == null) ? (QB::Field( "precedence" )) : $psOrder); 
	}
	
	protected function set_RefCount( )				{ return $this->Set("refcount", $psValue); }
	protected function get_RefCount( )				{ return $this->Get("refcount"); }

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
			{
				DB::EntityDescriptor("dich_datasets_fields")->Del( 
					QB::E("d_id", "=", $this->Id) 
				);
			
				if ( ($loDesc = $this->GetEntityDescriptor() ) != null )
					$loDesc->Drop();
				
				return true;
			}
		
		return false;
	}	

	public function Save()
	{
		if (Entity::Save())
		{
			$lsTableName = Dataset::GetDatasetDataTableName( $this );
			
			if ($this->GetEntityDescriptor() == null)
			{
				$descriptor = new EntityDescriptor( $lsTableName, "DatasetEntity");
				$descriptor
					-> DeclareField("r_id", "bigint")
        		    -> DeclareField("p_id", "bigint default '0'")
        		    -> DeclareField("precedence", "bigint default '0'");
					
				$descriptor->Deploy();
			}
		}
		
		return $this;
	}

	public function CleanupDataTable($pnResourceId)
	{
		if ( ($loDesc = $this->GetEntityDescriptor() ) != null )
			$loDesc->Del(
				QB::E("r_id", "=", $pnResourceId)
			);
	}
	
	public function GetEntityDescriptor()
	{
		return EntityDescriptor::FromTableWithCustomEntityClass( Dataset::GetDatasetDataTableName( $this ), "DatasetEntity" );
	}
	
	public static function GetDatasetDataTableName( Dataset $poDataset)
	{
		return  "dich_dataset_" . $poDataset->Id;
	}

	public static function GetDatasetDataTableNameById( $pnDataset)
	{
		return  "dich_dataset_" . $pnDataset;
	}
}

/* Модель сущности типичной контентной таблицы */
class DatasetEntity extends Entity
{
	protected function set_ParentId( $psValue )		{ $this->Set("p_id", $psValue); }
	protected function get_ParentId( )				{ return $this->Get("p_id"); }
	
    protected function set_Precedence( $psValue )	{ $this->Set("precedence", $psValue); }
	protected function get_Precedence( )			{ return $this->Get("precedence"); }

	public function DatasetEntity( EntityDescriptor $poDescriptor )
	{
	    Entity::Entity( $poDescriptor );
    }
    	
	private function _PrecedenceSelector()
	{
		return  QB::E("p_id", "=", $this->ParentId);
	}
	
	protected function BeforeInsert()				
	{ 
		$this->Precedence = EntityPrecedence::GetNextPrecedence( $this, $this->_PrecedenceSelector() );	

		return Entity::BeforeInsert();
	}
	
	public function PrecedenceDown()
	{
		EntityPrecedence::PrecedenceDown( $this, $this->_PrecedenceSelector() );
	}
	
	public function PrecedenceUp()
	{
		EntityPrecedence::PrecedenceUp( $this, $this->_PrecedenceSelector() );
	}

	public function Del($lbSkipPrecedenceCorrection = false)
	{
		if ( Entity::Del() )
		{
			// Поправим порядок
			if ( !$lbSkipPrecedenceCorrection )
				EntityPrecedence::PrecedenceRemove( $this, $this->_PrecedenceSelector() );
			
			return true;
		}
		
		return false;
	}			
}

/* Модель полей датасета */
class DatasetField extends Entity
{
	protected function get_DatasetId( )				{ return $this->Get("d_id"); }

	protected function set_Name( $psValue )			{ $this->Set("name", $psValue); }
	protected function get_Name( )					{ return $this->Get("name"); }

	protected function set_Title( $psValue )		{ $this->Set("title", $psValue); }
	protected function get_Title( )					{ return $this->Get("title"); }

	protected function set_Class( $psValue )		{ $this->Set("class", $psValue); }
	protected function get_Class( )					{ return $this->Get("class"); }

	protected function set_Precedence( $psValue )	{ $this->Set("precedence", $psValue); }
	protected function get_Precedence( )			{ return $this->Get("precedence"); }

	protected function set_Important( $psValue )	{ $this->Set("important", $psValue); }
	protected function get_Important( )				{ return $this->Get("important"); }

	protected function set_Fixed( $pbValue )		{ $this->Set("fixed", $pbValue === true ? "1" : "0"); }
	protected function get_Fixed( )					{ return $this->Get("fixed") == 1; }

	public function GatherFromPublicDomain($paValue)
	{
		foreach ($paValue as $lsKey=>$lsValue) switch ($lsKey)
		{
			case "name" : 		$this->Name = $lsValue; break;
			case "title" : 		$this->Title = $lsValue; break;
			case "class" : 		$this->Class = $lsValue; break;
			case "important" : 	$this->Important = $lsValue; break;
		}

		return Entity::GatherFromPublicDomain( $paValue );
	}

	protected function BeforeInsert()				
	{ 
		$this->Precedence = EntityPrecedence::GetNextPrecedence( $this, QB::E("d_id", "=", $this->DatasetId) );	

		return Entity::BeforeInsert();
	}
	
	public function PrecedenceDown()
	{
		EntityPrecedence::PrecedenceDown( $this, QB::E("d_id", "=", $this->DatasetId) );
	}
	
	public function PrecedenceUp()
	{
		EntityPrecedence::PrecedenceUp( $this, QB::E("d_id", "=", $this->DatasetId) );
	}

	public function ToggleImportance()
	{
		EntityImportance::Toggle( $this );
	}
	
	public function Del()
	{
		if (!$this->Fixed)
			if ( Entity::Del() )
			{
				if (($loDescriptor = EntityDescriptor::FromTable( Dataset::GetDatasetDataTableNameById( $this->DatasetId ) )) != null)
				{
					$loDescriptor -> RemoveField( $this->Name );
					$loDescriptor->Deploy();
				}
			
				// Поправим порядок
				EntityPrecedence::PrecedenceRemove( $this, QB::E("d_id", "=", $this->DatasetId) );
			
				return true;
			}
		
		return false;
	}	

	public function Save()
	{
		if (Entity::Save())
		{
			if (($loDescriptor = EntityDescriptor::FromTable( Dataset::GetDatasetDataTableNameById( $this->DatasetId ) )) != null)
			{
				$lsClassName = $this->Class;
				$loFieldClassInstance = new $lsClassName( $this, null );

				$loDescriptor -> DeclareField( $this->Name, $loFieldClassInstance->FieldType );
				$loDescriptor->Deploy();
			}
		}
		
		return $this;
	}
}

class DatasetFieldClass extends PropertyObject
{
	protected $_oDataAdapter = null;
	protected $_oDatasetField = "";

	public function DatasetFieldClass( $poDatasetField, $poDataAdapter )
	{
		$this->_oDataAdapter = $poDataAdapter;
		$this->_oDatasetField = $poDatasetField;
	}
	
	protected function get_Name() {	return $this->_oDatasetField->Name; }
	protected function get_HashString() { return "" . strval($this->_oDataAdapter->Resource->Id) . "_" . strval($this->_oDataAdapter->Entity->Id) . "_" . $this->Name; }
	protected function get_Hash() {	return "id" . md5( $this->HashString ); }
	protected function get_Title() { return $this->_oDatasetField->Title; }
	protected function get_FieldType() { return "text"; }
	protected function get_Important( )	{ return $this->_oDatasetField->Important; }

	
	// Для перегрузки
	protected function get_Value() {  }
	protected function set_Value( $psValue ) {  }
	public function DrawEditFormPart() { }
	public function ReceiveEditFormPart( $paSource ) { }
}


// Таблица датасетов
$descriptor = new EntityDescriptor( "dich_datasets", "Dataset");
$descriptor
	-> DeclareField("title", "text")
	-> DeclareField("name", "text")
	-> DeclareField("description", "text")
	-> DeclareField("bound", "boolean default 0")
	-> DeclareField("refcount", "bigint default '0'");
DB::RegisterEntityDescriptor( $descriptor);

// Таблица полей датасетов
$descriptor = new EntityDescriptor( "dich_datasets_fields", "DatasetField");
$descriptor
	-> DeclareField("d_id", "text")
	-> DeclareField("title", "text")
	-> DeclareField("name", "text")
	-> DeclareField("class", "text")
	-> DeclareField("precedence", "bigint default '0'")
	-> DeclareField("fixed", "boolean default '0'")
 	-> DeclareField("important", "bigint");
DB::RegisterEntityDescriptor( $descriptor);

// Список классов
include_once("assets/datasets.field.text.inc.php");
include_once("assets/datasets.field.bigtext.inc.php");
include_once("assets/datasets.field.dhtml.inc.php");
?>
