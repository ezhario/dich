<?
class DB
{
	private static $_oConnection = null;
	private static $_nUsedQueries = 0;
	private static $_aEntityDescriptors = array();
	
	public static function Connect ()
	{
		DB::$_oConnection = mysql_connect( Config::$DB["host"], Config::$DB["user"], Config::$DB["password"] );
	
		if ( !mysql_select_db( Config::$DB["database"], DB::$_oConnection ) ) DB::$_oConnection = null;
		else
		{
			mysql_set_charset( Config::$DB["charset"], DB::$_oConnection );
		}
	}
	
	public static function Connected ()		{ return DB::$_oConnection != null; }
	
	public static function Query ($psSQL)
	{
		DB::$_nUsedQueries ++;
		$loRes = mysql_query( $psSQL, DB::$_oConnection );
		
		if  ( $lsString = mysql_error(DB::$_oConnection) )
		{
			Log::PrintF("%s", $lsString);
			Log::PrintF("Error query: %s", $psSQL);
			return null;
		}

		return $loRes;
	}

	public static function QueryF ()
	{
		$loArgs = func_get_args();

		for ($i = 1; $i<count($loArgs); $i++)
			$loArgs[$i] = DB::Escape( $loArgs[$i] );
		
		$lsSQL = call_user_func_array("sprintf", $loArgs);
		
		return DB::Query( $lsSQL );
	}
	
	public static function Row ($poResult)				{ return mysql_fetch_assoc( $poResult ); }
	public static function Scalar($poResult)			{ return array_shift(mysql_fetch_assoc( $poResult )); }
	public static function RowCount ($poResult)			{ return mysql_num_rows( $poResult ); }
	public static function Escape ($psString)			{ return mysql_real_escape_string($psString, DB::$_oConnection); }
	public static function TestTable ($psTableName)		{ return DB::Query( "desc `$psTableName`" ) != null; }
	public static function InsertId ()					{ return mysql_insert_id( DB::$_oConnection ); }
	public static function Deploy ()					{ foreach (DB::$_aEntityDescriptors as $k=>$v) $v->Deploy(); }
	public static function RegisterEntityDescriptor( EntityDescriptor $poDescriptor )	{ DB::$_aEntityDescriptors[ $poDescriptor->sName ] = $poDescriptor; }
	public static function UnregisterEntityDescriptor( EntityDescriptor $poDescriptor ) { unset(DB::$_aEntityDescriptors[ $poDescriptor->sName ]); }
	public static function EntityDescriptor( $psName )
	{
		if (isset( DB::$_aEntityDescriptors[ $psName ] ))
			return DB::$_aEntityDescriptors[ $psName ];
			
		return null;
	}
	public static function ListTables()
	{
		$laResult = array();

		if ($loRes = DB::Query( QB::ShowTables() ))
			while ($loRow = DB::Row( $loRes ))
			{
				$loTmp = array_values($loRow);
				$laResult[] = $loTmp[0];
			}
				
		return $laResult;
	}
}

class EntityDescriptor
{
	public $aFields = array();
	public $aKeys = array();
	public $sName = "";
	public $sEntityClassName = "";
	
	public function EntityDescriptor($psName, $psEntityClassName, $pfClosure = null)
	{
		$this->sName  = $psName;
		
		$this->DeclareField("id", "bigint auto_increment primary key", true);
		
		$this->sEntityClassName = $psEntityClassName;
		
		if ($pfClosure != null)
			$pfClosure($this);
	}

	public function DeclareField($psFieldName, $psType = "text", $pbIsKey = false)
	{
		$this->aFields[ $psFieldName ] = $psType;
		
		if ($pbIsKey)
			$this->aKeys[] = $psFieldName;
			
		return $this;
	}
	
	public function RemoveField($psFieldName)
	{
		unset($this->aFields[ $psFieldName ]);
		$this->aKeys[] = array_diff( $this->aKeys, array($psFieldName) );	
	}
	
	public function Deploy()
	{
		// Проверим, есть ли эта таблица. Если есть - то посчитаем дифф.
		// Иначе тупо создадим
		
		if ( DB::Query( QB::Describe( $this->sName ) ) )
		{
			$loReference = EntityDescriptor::FromTable( $this->sName, true );		
			
			// Заполним диффы
			$laRemoved = array_diff( array_keys($loReference->aFields), array_keys($this->aFields) );
			$laAdded = array_diff( array_keys($this->aFields), array_keys($loReference->aFields) );
			
			$laTypeChanged = array();
			foreach ( $this->aFields as $lsField=>$lsType )
				if ( $loReference->aFields[ $lsField ] != $lsType )
					$laTypeChanged[] = $lsField;

			// Соберём запросы
			
			// Для удаления колонок
			foreach($laRemoved as $lsField)
				DB::Query( QB::Alter(
					QB::Table( $this->sName ),
					QB::DropColumn( $lsField )
				));
			
			// Для добавления новых колонок
			// * Тут когда-нибудь надо будет добавить дефолтный чарсет к мантре. Пляшем :)
			foreach($laAdded as $lsField)
				DB::Query( QB::Alter(
					QB::Table( $this->sName ),
					QB::AddColumn( QB::Field( $lsField, $this->aFields[ $lsField ] ))
				));
			
			// Для изменения типов
			foreach ($laTypeChanged as $lsField)
				DB::Query( QB::Alter(
					QB::Table( $this->sName ),
					QB::ModifyColumn( QB::Field( $lsField, $this->aFields[ $lsField ] ))
				));
				
			DB::RegisterEntityDescriptor($this);

		} else
		{
			$laFields = array();

			foreach( $this->aFields as $k => $v )
				$laFields[] = QB::Field($k, $v);
				
			DB::Query( QB::Create(	
				QB::Table( $this->sName ), 
				QB::Fields( $laFields ), 
				QB::DefaultCharset("utf8") 
			));
			
			DB::RegisterEntityDescriptor($this);
		}
	}
	
	public function EmptyEntity()
	{
		$lsTmp = $this->sEntityClassName;
		
		return new $lsTmp($this);
	}
	
	//Для совместимости
	public function GetEmpty()
	{
		return $this->EmptyEntity();
	}

	public function Del($psWhere = null)
	{
		return (DB::Query( QB::Delete(
			QB::From( QB::Table( $this->sName ) ),
			($psWhere != null) ? QB::Where( $psWhere ) : ""
		)) == null) ? false : true;
	}
	
	public function Drop()
	{
		DB::Query( QB::Drop( QB::Table( $this->sName )));
		DB::UnregisterEntityDescriptor($this);
	}

	public function Entities( $psSelector = null, $psOrder = null, $psLimit = null )
	{
		$laResult = array();
		$lsTmp = $this->sEntityClassName;
		
		foreach ( $this->FreeSelect( QB::All(), $psSelector, $psOrder, $psLimit ) as $loRow )
		{
			$loObj = new $lsTmp($this);
			$loObj->Load($loRow);
			$laResult[ $loObj->Id ] = $loObj;
		}
			
		return $laResult;
	}

	public function FreeSelect( $psFields = null, $psSelector = null, $psOrder = null, $psLimit = null )
	{
		$laResult = array();
		
		if ($loResult = DB::Query( QB::Select( 
				QB::ListFields( ($psFields!=null)? $psFields : QB::All() ), 
				QB::From( QB::Table( $this->sName ) ),
				($psSelector!=null)? QB::Where( $psSelector ) : "", 
				($psOrder!=null)? QB::Order( $psOrder ) : "",
				($psLimit!=null)? $psLimit : ""
		)))
		{
			while ($loRow = DB::Row( $loResult ))
				$laResult[ ] = $loRow;
		}
			
		return $laResult;
	}
	
	public static function FromTable( $psTable, $pbSkipCaching = false )
	{
		return EntityDescriptor::FromTableWithCustomEntityClass( $psTable, "Entity", $pbSkipCaching );
	}

	public static function FromTableWithCustomEntityClass( $psTable, $psEntityClass = "Entity", $pbSkipCaching = false )
	{
		if (!$pbSkipCaching)
		{
			$loDesc = DB::EntityDescriptor( $psTable );
		
			if ($loDesc != null)
				return $loDesc;
		}
		
		$loDesc = new EntityDescriptor( $psTable, $psEntityClass );
		
		if ($loResult = DB::Query( QB::Describe($psTable) ))
		{
			while ($loRow = DB::Row( $loResult ))
				$loDesc -> DeclareField( $loRow["Field"], $loRow["Type"] );
		
			DB::RegisterEntityDescriptor($loDesc);
			
			return $loDesc;
		}
		
		return null;		
	}
}

class QB
{
	public static function EAnd()							{ $laArr = func_get_args(); return "(" . implode(" and ", $laArr) . ")"; }
	public static function EOr()							{ $laArr = func_get_args(); return "(" . implode(" or ", $laArr) . ")"; }
	public static function E( $psField, $psOp, $psValue )	{ return "( `$psField` $psOp \"" . DB::Escape($psValue) . "\" )"; }
	public static function ShowTables()						{ return "SHOW TABLES"; }
	public static function Create()							{ $laArr = func_get_args(); return "CREATE TABLE " . implode(" ", $laArr); }
	public static function Insert()							{ $laArr = func_get_args(); return "INSERT INTO " . implode(" ", $laArr); }
	public static function Update()							{ $laArr = func_get_args(); return "UPDATE " . implode(" ", $laArr); }
	public static function Select()							{ $laArr = func_get_args(); return "SELECT " . implode(" ", $laArr); }
	public static function Delete()							{ $laArr = func_get_args(); return "DELETE " . implode(" ", $laArr); }
	public static function Drop($psTable)					{ return "DROP TABLE " . $psTable; }
	public static function DropColumn($psColumn)			{ return "DROP COLUMN " . $psColumn; }
	public static function AddColumn()						{ $laArr = func_get_args(); return "ADD COLUMN " . implode(" ", $laArr); }
	public static function ModifyColumn()					{ $laArr = func_get_args(); return "MODIFY " . implode(" ", $laArr); }
	public static function Describe($psTable)				{ return "DESCRIBE " . $psTable; }
	public static function Table( $psName ) 				{ return "`" . $psName . "`"; }
	public static function Alter( )							{ $laArr = func_get_args(); return "ALTER TABLE " . implode(" ", $laArr); }
	public static function Fields($psFieldsArray = null)
	{ 
		if (is_array($psFieldsArray))
			return "(" . implode(", ", $psFieldsArray) . ")"; 
			
		$laArr = func_get_args();
			
		return "(" . implode(", ", $laArr) . ")"; 
	}
	public static function DefaultCharset( $psCharset ) 	{ return "DEFAULT CHARSET " . $psCharset; }
	public static function Field( $psName, $psDescription = "") { return "`" . $psName . "` " . $psDescription; }
	public static function Value( $psName, $psValue) 		{ return "`$psName` = \"" . DB::Escape($psValue) . "\""; }
	public static function Set($psFieldsArray = null) 							
	{ 
		if (is_array($psFieldsArray))
			$laArr = $psFieldsArray;
		else
			$laArr = func_get_args(); 
			
		return "SET " . implode(", ", $laArr); 
	}
	public static function Where($psWhere) 					{ return "WHERE " . $psWhere; }
	public static function All() 							{ return "*"; }
	public static function ListFields() 					{ $laArr = func_get_args(); return implode(", ", $laArr); }
	public static function Func( $psName )					{ $laArr = func_get_args(); array_shift($laArr); return $psName . "(" . implode(", ", $laArr) . ")"; }
	public static function From()							{ $laArr = func_get_args(); return "FROM " . implode(", ", $laArr); }
	public static function Group()							{ $laArr = func_get_args(); return "GROUP BY " . implode(", ", $laArr); }
	public static function Order($psFieldsArray)							
	{ 
		if (is_array($psFieldsArray))
			return " ORDER BY " . implode(", ", $psFieldsArray) . ""; 

		$laArr = func_get_args(); return "ORDER BY" . implode(", ", $laArr); 
	}
	public static function Distinct()						{ return "DISTINCT"; }
	public static function Raw($psRaw)						{ return $psRaw; }
	public static function Limit($pnFrom, $pnCount = null)	{ $lsStr = ($pnCount == null)  ? "$pnFrom" : "$pnFrom, $pnCount"; return " LIMIT $lsStr"; }
}

/*
		QB::Create(
			QB::Table("dich_test_table"),
			QB::Fields(
				QB::Field("id", "bigint auto_increment primary key"),
				QB::Field("name", "text"),
				QB::Field("value", "bigint")
			),
			QB::DefaultCharset("utf8")
		)
		
		QB::Insert(
			QB::Table("dich_test_table"),
			QB::Fields(
				QB::Field("id"),
				QB::Field("name"),
				QB::Field("value")
			),
			QB::Set(
				QB::Value("id", "25"),
				QB::Value("name", "15"),
				QB::Value("value", "30")
			)
		)
		
		QB::Update(
			QB::Table("dich_test_table"),
			QB::Set(
				QB::Value("id", "25"),
				QB::Value("name", "15"),
				QB::Value("value", "30")
			),
			QB::Where(
				QB::EAnd(
					QB::EOr(
						QB::E("name", "like", "%hello%"),
						QB::E("name", "like", "%kitkat%")
					),
					QB::EOr(
						QB::E("value", ">", "10"),
						QB::E("value", "<", "15")
					),
					QB::E("d_id", "=", "12")
				)
			)
		)

		QB::Select(
			QB::Distinct(),
			QB::SelectFields(
				QB::Field("id"),
				QB::Func("COUNT",
					QB::All()
				)
			),
			QB::From(
				QB::Table("dich_test_table")
			),
			QB::Where(
				QB::EAnd(
					QB::EOr(
						QB::E("name", "like", "%hello%"),
						QB::E("name", "like", "%kitkat%")
					),
					QB::EOr(
						QB::E("value", ">", "10"),
						QB::E("value", "<", "15")
					),
					QB::E("d_id", "=", "12")
				)
			),
			QB::Order(
				QB::Field("name"),
				QB::Field("value", "DESC")
			),
			QB::Group(
				QB::Field("name"),
				QB::Field("value")
			)
		)
*/

abstract class PropertyObject
{
	public function __get($name)
	{
		if (method_exists($this, ($method = 'get_'.$name)))
			return $this->$method();
		else 
			return;
	}

	public function __isset($name)
	{
		if (method_exists($this, ($method = 'isset_'.$name)))
			return $this->$method();
		else 
			return;
	}

	public function __set($name, $value)
	{
		if (method_exists($this, ($method = 'set_'.$name)))
			$this->$method($value);
	}

	public function __unset($name)
	{
		if (method_exists($this, ($method = 'unset_'.$name)))
			$this->$method();
	}
}

class Entity extends PropertyObject
{
	public $_oDescriptor = null;
	protected $_aValues = array();
	protected $_aDirties = array();
	
	public function Entity(EntityDescriptor $poDescriptor)
	{
		$this->_oDescriptor = $poDescriptor;
		return $this;
	}
	
	public function Set($psFieldName, $psValue, $pbOverride = false)
	{
		if (!isset($this->_oDescriptor->aFields[ $psFieldName ]))
			return $this;
			
		if ( !$pbOverride )
			if (strtolower( $psFieldName ) == "id") 
				return $this;
			
		if ($this->_aValues[ $psFieldName ] != $psValue)
		{
			$this->_aValues[ $psFieldName ] = $psValue;
			$this->_aDirties[ $psFieldName ] = true;
		}
		
		return $this;
	}
	
	public function Get($psFieldName)
	{
		if (isset($this->_aValues[ $psFieldName ]))
			return $this->_aValues[ $psFieldName ];
			
		return null;
	}
	
	public function GatherFromPublicDomain($paValue)
	{
		return $this;
	}

	public function Load($pnoIndexValueOrFullRecord)
	{
		if (is_array( $pnoIndexValueOrFullRecord ))
		{
			foreach ($pnoIndexValueOrFullRecord as $k => $v)
				$this->_aValues[ $k ] = $v;
				
			$this->_aDirties = array();
			
			return $this;
		} else
		{
			if ($loResult = DB::Query( QB::Select( 
				QB::All(), 
				QB::From( QB::Table($this->_oDescriptor->sName) ), 
				QB::Where( QB::E("id", "=", $pnoIndexValueOrFullRecord)	)
			)))
				if ($loRow = DB::Row( $loResult ))
					return $this->Load($loRow);
		}
		
		return null;
	}
	
	public function Save()
	{
		$laFields = array();
		
		if ( !isset($this->_aValues["id"]) )
		{
			if ($this->BeforeInsert())
			{
				foreach( $this->_oDescriptor->aFields as $k => $v )
					if (isset($this->_aDirties[ $k ]) && ($this->_aDirties[ $k ] == true))
						if ($k != "id")
							$laFields[] = QB::Value( $k, $this->_aValues[ $k ] );

				DB::Query( QB::Insert(
					QB::Table( $this->_oDescriptor->sName ),
					QB::Set( $laFields )
				));
				
				$this->Set( "id", DB::InsertId(), true );
				
				$this->OnInsert();
			}
		}
		else
		{
			foreach( $this->_oDescriptor->aFields as $k => $v )
				if (isset($this->_aDirties[ $k ]) && ($this->_aDirties[ $k ] == true))
					if ($k != "id")
						$laFields[] = QB::Value( $k, $this->_aValues[ $k ] );

			if ($this->BeforeUpdate())
			{
				DB::Query( QB::Update(
					QB::Table( $this->_oDescriptor->sName ),
					QB::Set( $laFields ),
					QB::Where(
						QB::E("id", "=", $this->_aValues["id"])
					)
				));
				
				$this->OnUpdate();
			}
		}
		
		$this->Dirties = array();
		
		return $this;
	}
	
	public function ForceSetDirty( $psField, $pbSetDirty )
	{
		if ($pbIsDirty)
		{
			$this->_aDirties[ $psField ] = true;
		}else
		{
			unset( $this->_aDirties[ $psField ] );
		}
	}	
	
	public function Del()
	{
		if ( isset($this->_aValues["id"]) )
			return $this->_oDescriptor->Del( QB::E("id", "=", $this->_aValues["id"]) );
		
		return false;
	}
	
	protected function BeforeUpdate()				{ return true; }
	protected function BeforeInsert()				{ return true; }
	protected function OnInsert()					{}
	protected function OnUpdate()					{}
	
	protected function isset_Id( )					{ return isset( $this->_aValues["id"] ); }
	protected function get_Id( )					{ return $this->Get("id"); }
	
}

abstract class	EntityPrecedence
{
	public static function GetNextPrecedence($poThis, $poSelector)
	{
		return DB::Scalar( DB::Query( QB::Select (
			QB::Func("COUNT", QB::All() ),
			QB::From( QB::Table($poThis->_oDescriptor->sName) ),
			QB::Where( $poSelector )		
		) ) );
	}

	public static function PrecedenceRemove($poThis, $poSelector)
	{
		DB::Query( QB::Update( 
			QB::Table($poThis->_oDescriptor->sName), 
			QB::Set( QB::Raw("`precedence` = `precedence` - 1") ),
			QB::Where( 
				QB::EAnd( 
					$poSelector, 
					QB::E("precedence", ">", $poThis->Precedence)
				)
			)
		));
	}
	
	public static function PrecedenceDown($poThis, $poSelector)
	{
		DB::Query( QB::Update( 
			QB::Table($poThis->_oDescriptor->sName), 
			QB::Set( QB::Raw("`precedence` = `precedence` - 1") ),
			QB::Where( 
				QB::EAnd( 
					$poSelector, 
					QB::E("precedence", "=", $poThis->Precedence + 1)
				)
			),
			QB::Limit( 1 )
		));
		
		DB::Query( QB::Update( 
			QB::Table($poThis->_oDescriptor->sName),
			QB::Set( QB::Raw("`precedence` = `precedence` + 1") ),
			QB::Where( QB::E("id", "=", $poThis->Id) )
		));
		
		$poThis->Precedence = $poThis->Precedence + 1;
	}
	
	public static function PrecedenceUp($poThis, $poSelector)
	{
		DB::Query( QB::Update( 
			QB::Table($poThis->_oDescriptor->sName),
			QB::Set( QB::Raw("`precedence` = `precedence` + 1") ),
			QB::Where( 
				QB::EAnd(
					$poSelector, 
					QB::E("precedence", "=", $poThis->Precedence - 1)
				)
			),
			QB::Limit( 1 )
		));		
		
		DB::Query( QB::Update( 
			QB::Table($poThis->_oDescriptor->sName),
			QB::Set( QB::Raw("`precedence` = `precedence` - 1") ),
			QB::Where( QB::E("id", "=", $poThis->Id) )
		));

		$poThis->Precedence = $poThis->Precedence - 1;
	}	
}

abstract class EntityImportance
{
	public static function Toggle($poThis)
	{
		DB::Query( QB::Update( 
			QB::Table($poThis->_oDescriptor->sName),
			QB::Set( QB::Value("important", ($poThis->Important == 1) ? "0" : "1" ) ),
			QB::Where( QB::E("id", "=", $poThis->Id) )
		));
		
		$poThis->Important =  ($poThis->Important == 1) ? "0" : "1" ;
	}
}

abstract class EntityReferencing
{
	public static function Ref($poThis)
	{ 
		$lsSQL = QB::Update( 
			QB::Table($poThis->_oDescriptor->sName),
			QB::Set( QB::Raw("`refcount` = `refcount` + 1") ),
			QB::Where( QB::E("id", "=", $poThis->Id) )
		);
		
		DB::Query( $lsSQL );
		
		$poThis->Refcount = $poThis->Refcount + 1;
		$poThis->ForceSetDirty("refcount", false);
	}

	public static function Unref($poThis)
	{
		if ($poThis->Refcount > 0)
		{
			DB::Query( QB::Update( 
				QB::Table($poThis->_oDescriptor->sName),
				QB::Set( QB::Raw("`refcount` = `refcount` - 1") ),
				QB::Where( QB::EAnd(
					QB::E("id", "=", $poThis->Id),
					QB::E("refcount", ">", "0")
				))
			));
		
			$poThis->Refcount = $poThis->Refcount - 1;
			$poThis->ForceSetDirty("refcount", false);
		}
	}
}

DB::Connect();
?>
