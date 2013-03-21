<?
/* Модель структуры */
class Structure
{
	/* Хранилище классов логики */
	protected static $_aLogicClasses = array();
	
	public static function RegisterLogicClass( $psClassName, $psTitle )	{ Structure::$_aLogicClasses[$psClassName] = $psTitle; }
	public static function GetLogicClasses()	{ return Structure::$_aLogicClasses;	}

	/* Методы получения контента */
	public static function GetEmpty()									{ return DB::EntityDescriptor("dich_content")->EmptyEntity(); }
	public static function Get( $pnId ) 								{ return DB::EntityDescriptor("dich_content")->EmptyEntity()->Load($pnId); }

    protected static $_aCachedAllEnabledContent = null;

	// Этот метод возвращает НЕ ВСЕ разделы, а толко КОНТЕНТНЫЕ.
	// Для получения сервисных разделов необходимо воспользоваться методом
	// Structure::AllServices( ... )
	public static function All( $psSelector = null, $psOrder = null )	
	{ 
		return Structure::AllContent( $psSelector, $psOrder );
	}

	// Метод возвращает все разрешённые для показа разделы
	public static function AllEnabled( $psSelector = null, $psOrder = null )	
	{ 
	    $lbEmptyArgs = ($psSelector == null) && ($psOrder == null);
	
	    if ( $lbEmptyArgs && (Structure::$_aCachedAllEnabledContent != null))
	        return Structure::$_aCachedAllEnabledContent;
	
		$loTmpSelector = QB::E("disabled", "=", "0");	
	
		$lsSelector = ( $psSelector === null ) 
			? $loTmpSelector
			: QB::EAnd( $loTmpSelector, $psSelector );
	
		$laTmpValue = Structure::All( $lsSelector, $psOrder);
		
		if ($lbEmptyArgs) 
		{   
		    Structure::$_aCachedAllEnabledContent = $laTmpValue;
		}
		
		return $laTmpValue;
	}
	
	public static function AllContent( $psSelector = null, $psOrder = null )
	{
		$lsOrder = null;
	
		if ($psOrder == null)
		{
			$lsOrder = array(
				QB::Field("p_id"),
				QB::Field("precedence")
			);
		}
		
		$loTmpSelector = QB::E("is_service", "=", "0");

		$lsSelector = ( $psSelector === null ) 
			? $loTmpSelector
			: QB::EAnd( $loTmpSelector, $psSelector );
	
		return DB::EntityDescriptor("dich_content")->Entities( $lsSelector, $lsOrder ); 
	}
	
	public static function AllServices( $psSelector = null, $psOrder = null )
	{
		$lsOrder = null;
	
		if ($psOrder == null)
			$lsOrder = array( QB::Field("precedence") );
		
		$loTmpSelector = QB::E("is_service", "=", "1");

		$lsSelector = ( $psSelector === null ) 
			? $loTmpSelector
			: QB::EAnd( $loTmpSelector, $psSelector );
	
		return DB::EntityDescriptor("dich_content")->Entities( $lsSelector, $lsOrder ); 		
	} 
	
    protected static $_aCachedAllEnabledHierarchy = null;
	
	// Если скармливаете этому методу закэшированные данные — будьте внимательны!
	// Тут нет проверок на признак сервиса, поэтому есть шанс получить на выходе 
	// немножко ада и чуть-чуть земли обетованной. Наслаждайтесь! :)
	public static function Hierarchy( $psArgument = null)			
	{ 
	    if ($psArgument === Structure::$_aCachedAllEnabledContent)
	        if (Structure::$_aCachedAllEnabledHierarchy != null)
	            return Structure::$_aCachedAllEnabledHierarchy;
	
		$laArgs = array();
	
		// Если передаём список разделов - построим по нему
		if (is_array($psArgument))
		{
			foreach ($psArgument as $loId=>$loValue)
				$laArgs[] = array( "id" => $loId, "p_id" => $loValue->ParentId, "precedence" => $loValue->Precedence );
		}else
		// Иначе это будет строка сортировки
		{
			foreach ( Structure::AllContent( null, ($psArgument == null) ? (QB::Field( "title" )) : $psArgument ) as $loId=>$loValue )
				$laArgs[] = array( "id" => $loId, "p_id" => $loValue->ParentId, "precedence" => $loValue->Precedence );
		}
		
		$laResult = array();
		
		// Построим иерархию
		foreach ($laArgs as $loRow)
		{
			if (!isset( $laResult[ $loRow["p_id"] ] ))
				$laResult[ $loRow["p_id"] ] = array();
				
			$laResult[ $loRow["p_id"] ] [] = $loRow["id"];
		}

	    if ($psArgument === Structure::$_aCachedAllEnabledContent)
	        Structure::$_aCachedAllEnabledHierarchy = $laResult;
	
		return $laResult;
	}
	
    public static function ContentAt($psURL)
    {
        $laAll = Structure::AllEnabled();
        $laHierarchy = Structure::Hierarchy( $laAll );
        $loURL = Net::URL($psURL);
        $laElements = $loURL->GetElements();
        array_unshift( $laElements, "");

        $loResult = Structure::SearchContentHierarchyWalker( $laAll, $laHierarchy, 0, 0,  $laElements); 
     
        return $loResult;
    }

    protected static function SearchContentHierarchyWalker( $paAll, $paHierarchy, $pnId, $pnLevel, $paElements )
    {
        $lnTotalElements = count($paElements);

        if ($pnLevel >= $lnTotalElements)
            return null;
    
    	foreach ($paHierarchy[ $pnId ] as $lnId)
    	{
    		if (($loContent = $paAll[$lnId]) != null)
    		{
    			if ($loContent->Name == $paElements[$pnLevel])
    			{
    			    if ($pnLevel == ($lnTotalElements - 1))
    			        return Structure::Get($lnId);
    			    
    			    return Structure::SearchContentHierarchyWalker( $paAll, $paHierarchy, $lnId, $pnLevel + 1, $paElements );
    			}
	    	}
    	}
    	
    	return null;
    }
}

/* Модель контента */
class Content extends Entity
{
	protected function set_ParentId( $psValue )		{ $this->Set("p_id", $psValue); }
	protected function get_ParentId( )				{ return $this->Get("p_id"); }
	
	protected function set_Title( $psValue )		{ $this->Set("title", $psValue); }
	protected function get_Title( )					{ return $this->Get("title"); }

	protected function set_Disabled( $psValue )		{ $this->Set("disabled", $psValue); }
	protected function get_Disabled( )				{ return $this->Get("disabled"); }

	protected function set_IsService( $psValue )	{ $this->Set("is_service", $psValue); }
	protected function get_IsService( )				{ return $this->Get("is_service"); }

	protected function set_IsMenuEntry( $psValue )	{ $this->Set("is_menu_entry", $psValue); }
	protected function get_IsMenuEntry( )			{ return $this->Get("is_menu_entry"); }

	protected function set_IsContent( $psValue )	{ $this->Set("is_service", !$psValue); }
	protected function get_IsContent( )				{ return !$this->Get("is_service"); }
	
	protected function set_Name( $psValue )			{ $this->Set("name", trim($psValue)); }
	protected function get_Name( )					{ return $this->Get("name"); }
	
	protected function set_MenuEntry( $psValue )	{ $this->Set("menu_entry", $psValue); }
	protected function get_MenuEntry( )				{ return $this->Get("menu_entry"); }

	protected function set_Meta( $psValue )			{ $this->Set("meta", $psValue); }
	protected function get_Meta( )					{ return $this->Get("meta"); }
	
	protected function get_Class( )					{ return $this->Get("class"); }
	
	protected function set_Precedence( $psValue )	{ $this->Set("precedence", $psValue); }
	protected function get_Precedence( )			{ return $this->Get("precedence"); }

	public function GatherFromPublicDomain($paValue)
	{
    	$this->ParentId		= $paValue["p_id"];
    	$this->Title 		= $paValue["title"];
    	$this->MenuEntry	= $paValue["menu_entry"];
    	$this->Name 		= $paValue["name"];
    	$this->Disabled		= $paValue["disabled"];
   		$this->IsService 	= $paValue["is_service"];
   		$this->IsMenuEntry  = $paValue["is_menu_entry"];
		
		return Entity::GatherFromPublicDomain( $paValue );
	}

	private function _PrecedenceSelector()
	{
		return  ( $this->IsService )
			? QB::E("is_service", "=", "1")
			: QB::EAnd(
				QB::E("p_id", "=", $this->ParentId),
				QB::E("is_service", "=", "0")
			);
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
			
			$loLogic = $this->Logic();

			// Отсоединим инстанс логики класса от сущности
			if ( $loLogic != null )
				$loLogic->Unlink();
		
			// Удалим детишек
			if ( $this->IsContent )
			{
				$laList = Structure::All( QB::E("p_id", "=", $this->Id) );
	
				// Удалим НЕ корректируя последовательность детей. 
				// Потому что незачем, мва-ха-ха!
				foreach ($laList as $lnListId => $loContent)
					$loContent->Del( true );
			}
				
			return true;
		}
		
		return false;
	}	

	public function DelWithChildrenShift()
	{
		$lnId = $this->Id;
		$lnParentId = $this->ParentId;
		
		if ( Entity::Del() )
		{
			// Поправим порядок
			EntityPrecedence::PrecedenceRemove( $this,  $this->_PrecedenceSelector() );

			$lnTotalOfParent = EntityPrecedence::GetNextPrecedence( $this,  $this->_PrecedenceSelector() ) + 1;

			DB::Query( QB::Update(
				QB::Table( $this->_oDescriptor->sName ),
				QB::Set( 
					QB::Value( "p_id", $lnParentId ),
					QB::Value( "precedence", "precedence + " . $lnTotalOfParent )  
				),
				QB::Where( QB::E("p_id", "=", $lnId) )
			));
			
			$loLogic = $this->Logic();

			if ($loLogic != null)
				$loLogic->Unlink();
			
			return true;
		}
		
		return false;
	}	
	
	// * В будущем надо будет добавить миграцию, а то на кой хрен ВСЁ ЭТО понаписано?
	// * Добавить проверку на существование класса
	
	public function ChangeLogic( $psNewClass )
	{
		$loOldLogic = $this->Logic();
		
		// Пока это вот так тупо.
		if ($loOldLogic != null)
			$loOldLogic->Unlink();
		
		$this->Set("class", $psNewClass);
		$this->Save();
		
		// Превозможем кэширование
		$this->_oLogic = null;
		
		$loNewLogic = $this->Logic();
		
		// И тут ничуть не умнее
		if ($loNewLogic != null)
			$loNewLogic->Deploy();
	}
	
	protected $_oLogic = null;
	
	public function Logic()
	{
		if ($this->_oLogic != null) return $this->_oLogic;
	
		$lsClassName = $this->Class;
		
		// Нет класса - нет и логики, внучок
		if ( ($lsClassName == "") || ($lsClassName == null) )
			return null;
		
		// Создадим объект
		$this->_oLogic = new $lsClassName( $this );
		
		return $this->_oLogic;
	}
}

/* Модель ресурсов */
class Resources
{
	/* Методы получения ресурсов */
	public static function GetEmpty( $pnContentId, $pnDatsetId = 0, $pnStencilId = 0, $pnParentId = 0)
	{ 
		$loResource = DB::EntityDescriptor("dich_resources")->EmptyEntity();
		
		$loResource->ContentId = $pnContentId;
		$loResource->DatasetId = $pnDatsetId;
		$loResource->StencilId = $pnStencilId;
		$loResource->ParentId = $pnParentId;
		
		return $loResource;
	}
	public static function Get( $pnId ) 								{ return DB::EntityDescriptor("dich_resources")->EmptyEntity()->Load($pnId); }
	public static function All( $pnContentId, $pnParent = 0 )	
	{ 
		$lsSelector = "";
	
		if ($pnParent == 0)
			$lsSelector = QB::E("c_id", "=", $pnContentId);
		else
			$lsSelector = QB::EAnd(
				QB::E("c_id", "=", $pnContentId),
				QB::E("p_id", "=", $pnParent)
			);
			
		// Немного грязный хак	
		return DB::EntityDescriptor("dich_resources")->Entities( $lsSelector, array( QB::Field("id", "ASC") ) ); 
	}
	
	public static function Enumerate( $paResourceList, $pnContentId, $pnDatasetId = -1, $pnStencilId = -1, $pnParentId = -1)
	{
		$laResults = array();
	
		foreach ($paResourceList as $loResource)
		{
			$lnDatasetId = ($loResource->DatasetId == null) ? 0 : $loResource->DatasetId;
			$lnStencilId = ($loResource->StencilId == null) ? 0 : $loResource->StencilId;
			$lnParentId = ($loResource->ParentId == null) ? 0 : $loResource->ParentId;
		
			if  ( 
				($loResource->ContentId == $pnContentId) && 
				( (($pnDatasetId == -1) && ($lnDatasetId != 0)) || ($lnDatasetId == $pnDatasetId) ) &&
				( (($pnStencilId == -1) && ($lnStencilId != 0)) || ($lnStencilId == $pnStencilId) ) &&
				( (($pnParentId == -1) && ($lnParentId != 0)) || ($lnParentId == $pnParentId) )
			)
				$laResults[] = $loResource;
		}
		
		return $laResults;
	}
}
	
/* Модель ресурса */
class Resource extends Entity
{
	protected function set_ParentId( $psValue )		{ $this->Set("p_id", $psValue); }
	protected function get_ParentId( )				{ return $this->Get("p_id"); }

	protected function set_Tag( $psValue )			{ $this->Set("tag", $psValue); }
	protected function get_Tag( )					{ return $this->Get("tag"); }
	
	protected function set_ContentId( $psValue )	{ $this->Set("c_id", $psValue); }
	protected function get_ContentId( )				{ return $this->Get("c_id"); }
	
	protected function get_Dataset() 
	{
		if ( ($this->DatasetId != 0) && ($this->DatasetId != null))
			return Datasets::Get($this->DatasetId);
		
		return null;
	}
	
	protected function get_Stencil()
	{
		if ( ($this->StencilId != 0) && ($this->StencilId != null) )
			return Stencils::Get($this->StencilId);
			
		return null;			
	}
	
	protected function set_DatasetId( $psValue )	{ $this->Set("d_id", $psValue); }
	protected function get_DatasetId( )				{ return $this->Get("d_id"); }
	
	protected function set_StencilId( $psValue )	{ $this->Set("st_id", $psValue); }
	protected function get_StencilId( )				{ return $this->Get("st_id"); }
	
	protected function set_Settings( $paValue )		{ $this->Set("settings", serialize($paValue) ); }
	protected function get_Settings( )				{ return unserialize( $this->Get("settings") ); }

	protected function set_Transformer( $psValue )	{ $this->Set("transformer", $psValue ); }
	protected function get_Transformer( )			{ return $this->Get("transformer"); }
	
	protected $_oDataAdapter = null;
	
	protected function get_DataAdapter()			
	{ 
		// Типа кэширование :)
		if ($this->_oDataAdapter == null)
			$this->_oDataAdapter = new ResourceDataAdapter( $this );
		
		return $this->_oDataAdapter; 
	}

	protected function OnInsert()
	{
		if ( ($loStencil = $this->Stencil) != null)
				$loStencil->Ref();

		if ( ($loDataset = $this->Dataset) != null)
				$loDataset->Ref();
	}

	public function Del()
	{
		if (Entity::Del())
		{
			if ( ($loStencil = $this->Stencil) != null)
				$loStencil->Unref();

			if ( ($loDataset = $this->Dataset) != null)
			{
				// Если датасет привязан - то убьём его вместе с ресурсом
				if ($loDataset->IsBound)
				{
					$loDataset->Unref();
					$loDataset->Del();
				}
				// Иначе просто вычистим содержимое
				else
				{
					$loDataset->CleanupDataTable( $this->Id );
					$loDataset->Unref();
				}
			}
			
			return true;
		}
		
		return false;
	}	
	
	public function StandaloneDataAdapter( $poEntity )			
	{ 
		$loAdapter = new ResourceDataAdapter( $this );
		
		if ($loAdapter != null)
			$loAdapter->Entity = $poEntity;
		
		return $loAdapter; 
	}
	
	//=============================
	// Доступ к ресурсным таблицам
	//=============================
	
	public function EmptyEntity()
	{
		if ( ($loDataset = $this->Dataset) != null)
		{
			$loResult = $loDataset->GetEntityDescriptor()->GetEmpty();
			$loResult->Set( "r_id", $this->Id );
			
			return $loResult;
		}
		
		return null;
	}
	
	//Для совместимости
	public function GetEmpty()
	{
		return $this->EmptyEntity();
	}

	public function Entities( $psSelector = null, $psOrder = null, $psLimit = null )
	{
		if ( ($loDataset = $this->Dataset) != null)
		{
			$lsRestriction = QB::E("r_id", "=", $this->Id);
			$lsSelector = ($psSelector!=null)? QB::EAnd( $psSelector, $lsRestriction) : $lsRestriction;
			
			return $loDataset->GetEntityDescriptor()->Entities( $lsSelector, $psOrder, $psLimit );
		}
		
		return array();
	}

	public function FreeSelect( $psFields = null, $psSelector = null, $psOrder = null, $psLimit = null )
	{
		if ( ($loDataset = $this->Dataset) != null)
		{
			$lsRestriction = QB::E("r_id", "=", $this->Id);
			$lsSelector = ($psSelector!=null)? QB::EAnd( $psSelector, $lsRestriction) : $lsRestriction;
			
			return $loDataset->GetEntityDescriptor()->FreeSelect( $psFields, $lsSelector, $psOrder, $psLimit );
		}
		
		return array();
	}
	
	public function ThroughTransformer( $poEntity)
	{
		$laArgs = array();
		
		foreach ($this->StandaloneDataAdapter( $poEntity )->Fields as $lsFieldName => $loField)
			$laArgs[ $lsFieldName ] = $loField;
			
		return Templates::TextTemplate( '?>' . $this->Transformer . '<?', $laArgs );
	}

	public function ThroughProductor( $paArgs )
	{
		return Templates::TextTemplate( '?>' . $this->Transformer . '<?', $paArgs );
	}
}

class ResourceDataAdapter extends PropertyObject
{
	protected $_oEntity = null;
	protected $_oResource  = null;
	protected $_oFieldCache = array();

	protected function get_Resource() { return $this->_oResource; }
	protected function set_Entity( $poEntity ) { $this->_oEntity = $poEntity; }
	protected function get_Entity() { return $this->_oEntity; }
	protected function get_Fields() { return $this->_oFieldCache; }

	public function ResourceDataAdapter(Resource $poResource)
	{
		// Если фальшивый экземпляр датасета - мы его превозможем
		if ($poResource == null) return;
		if ($poResource->DatasetId == null) return;

		// Обновим кэш		
		$this->_oResource = $poResource;
		$this->RebuildFieldCache();
	}
	
	protected function RebuildFieldCache()
	{
		// Проверка на венерические заболевания
		if ($this->_oResource == null)
		{
			$this->_oFieldCache = array();
			return;
		}		
				
		// Достанем всё из кэша
		foreach ( $this->_oResource->Dataset->AllFields() as $lnFieldId => $loField )
		{
			$lsClassName = $loField->Class;
			$this->_oFieldCache[ $loField->Name ] = new $lsClassName( $loField, $this );
		}
	}
	
	public function __get($lsName)
	{
	    // Высший приоритет отдадим локальным свойствам, по аналогии с PropertyObject
		if (method_exists($this, ($lsMethod = 'get_'.$lsName)))
			return $this->$lsMethod();
			
	    // Если не нашли такого локального свойства — попробуем найти такое поле 
	    $lsLowerName = strtolower($lsName);

        if (isset( $this->_oFieldCache[ $lsLowerName ] ))
            return $this->_oFieldCache[ $lsLowerName ]->Value;
                               
        // Если не нашли такого поля — то отчаемся и попробуем дёрнуть свойство привязанной сущности
        if ($this->_oEntity != null)
            return $this->_oEntity->$lsName;

        // Сдаёмся :(        	
	    return null;
	}


	public function Field( $psField ) {	return $this->_oFieldCache[ $psField ]; }
}

// Класс логики контента
class StructureLogic extends PropertyObject
{
	// Ссылка на контент
	protected $_oContent = null;
	// Основной ресурс логики. Можно в нём хранить всякую лабуду.
	protected $_oMainResource = null;
	
	// Аксессор для основного ресурса
	protected function get_MainResource() { return $this->_oMainResource; }
	// Аксессор для контента
	protected function get_Content() { return $this->_oContent; }

	// Конструктор. В качестве параметра принимает ссылку на контент.
	public function StructureLogic( $poContent )
	{
		// Установим ссылку на контент
		$this->_oContent = $poContent;	
		// Прочитаем список ресурсов
		$laResourcesList = Resources::All( $this->_oContent->Id );	
		// Перечислим корневые ресурсы
		$laRoot = Resources::Enumerate($laResourcesList, $this->_oContent->Id, 0,0,0);
		// Установим корневой ресурс
		$this->_oMainResource = (count($laRoot) > 0) ? $laRoot[0] : null;
		// Выполним обработчик инициализации
		$this->OnInit( $laResourcesList );
	}

	protected function set_Settings( $paValue )		{ $this->_oMainResource->Settings = $paValue; }
	protected function get_Settings( )				{ return $this->_oMainResource->Settings; }
	public function SaveSettings() { $this->_oMainResource->Save(); }
	
	// Метод размещения в базе
	public function Deploy()
	{
		$this->_oMainResource = Resources::GetEmpty( $this->_oContent->Id );
		$this->OnDeploy();
		$this->_oMainResource->Save();
	}
	
	// Метод вычёркивания из базы
	public function Unlink()
	{
		// Сначала дадим логике видимость шанса повлиять на мир
		$this->OnUnlink();		
	
		// А потом жестоко в это разочаруем. Убивать ресурсы, это СПАРТААА!
		foreach ( Resources::All( $this->_oContent->Id ) as $loResource )
			$loResource->Del();
	}
	
	public function Resources()
	{
		return Resources::All( $this->_oContent->Id );
	}
	
	public function DrawSettingsForm()
	{
		return $this->OnDrawSettingsForm();
	}
		
	public function ReceiveSettingsFormResult( $paFrom )
	{
		$this->OnReceiveSettingsFormResult( $paFrom );
	}	

	public function IsEditingSupported()
	{
		return true;
	}
	
	protected function OnInit( $paResources )
	{
	}

	protected function OnDeploy( )
	{
	}

	protected function OnUnlink( )
	{
	}

	public function RouteSettingsEditing()
	{
	}

	public function RouteEditing()
	{
	}
	
	public function RouteContentRendering()
	{
	}
}

/* Описатель таблицы контента */
$descriptor = new EntityDescriptor( "dich_content", "Content" );
$descriptor
	-> DeclareField("p_id", "bigint default '0'")	// Ссылка на родителя
	-> DeclareField("title", "text")				// Название
	-> DeclareField("menu_entry", "text")			// Название для разных меню
	-> DeclareField("is_menu_entry", "boolean default '1'")	// Попадает ли в меню
	-> DeclareField("name", "text")					// Часть УРЛа
	-> DeclareField("meta", "text")					// Метаданные
	-> DeclareField("class", "text")				// Класс контента
	-> DeclareField("disabled", "boolean default '0'")// Флаг "работает / нет"	
	-> DeclareField("is_service", "boolean default '0'") // Признак сервиса
	-> DeclareField("precedence", "bigint");		// Порядок
DB::RegisterEntityDescriptor( $descriptor);

/* Описатель таблицы ресурсов */
$descriptor = new EntityDescriptor( "dich_resources", "Resource" );
$descriptor
	-> DeclareField("c_id", "bigint")				// Ссылка на контент
	-> DeclareField("d_id", "bigint")				// Ссылка на датасет
	-> DeclareField("st_id", "bigint")				// Ссылка на шаблон
	-> DeclareField("p_id", "bigint default '0'")	// Ссылка на родителя
	-> DeclareField("tag", "bigint default '0'")	// Произвольная информация для контента
	-> DeclareField("transformer", "text")			// Код трансформатора / Продуктора
	-> DeclareField("settings", "text");			// Настройки
DB::RegisterEntityDescriptor( $descriptor);

// Подключим логики
include_once("assets/structure.logic.page.inc.php");
include_once("assets/structure.logic.redirector.inc.php");
include_once("assets/structure.logic.hierarchy.inc.php");
?>
