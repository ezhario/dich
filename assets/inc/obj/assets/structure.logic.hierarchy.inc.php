<?
class StructureLogicClassHierarchy extends StructureLogicClassPage
{
	/************
	  Поля
	*************/
	
	// Переменные
	protected $_aDatasetResources = array();
	protected $_aDatasets = array();
	protected $_aStencilResources = array();
	protected $_aStencils = array();
	
	// Аксессоры
	protected function get_DatasetResources() { return $this->_aDatasetResources; }
	protected function get_StencilResources() { return $this->_aStencilResources; }
	protected function get_Datasets() { return $this->_aDatasets; }
	protected function get_Stencils() { return $this->_aStencils; }
	
	protected function get_Structure() { return $this->_aDatasetResources; }

	/* Стандартные методы класса логики */	
	
	public function StructureLogicClassHierarchy( $poContent )
	{
		StructureLogicClassPage::StructureLogicClassPage( $poContent );
	}

	protected function OnInit( $paResources )
	{
		StructureLogicClassPage::OnInit( $paResources );
		
		// Подгрузим данные и структуру
		$this->_aDatasetResources = Resources::Enumerate( $paResources, $this->Content->Id, -1, 0, -1 );
		
		$this->_aStencilResources = Resources::Enumerate( $paResources, $this->Content->Id, 0, -1, -1 );
		
		foreach( $this->_aDatasetResources as $loResource)
			$this->_aDatasets[] = Datasets::Get( $loResource->DatasetId );
			
		foreach( $this->_aStencilResources as $loResource)
			$this->_aStencils[$loResource->ParentId] = Stencils::Get( $loResource->StencilId );
	}

	protected function OnUnlink( )
	{
		// Попросим родителя прибраться
		StructureLogicClassPage::OnUnlink();
		
		// И сами тоже
	}
	
	/****************************************
		Шрутизация 
	*****************************************/

    // Маршрутизация настроек логики
	public function RouteSettingsEditing( $psLevel1 )
	{

		/* Сохранение настроек */ if ($psLevel1 == "save")
		{
			$lnOldStencilId = $this->StencilId;
			$lnNewStencilId = Net::PostResult( "stencil_id" );

			if ($lnOldStencilId != $lnNewStencilId)
			{
				if ($lnOldStencilId != 0)
				{
					$this->StencilResource->Del();
					$this->StencilResource = null;
				}

				if ($lnNewStencilId != 0)
				{
					$this->StencilResource = Resources::GetEmpty( $this->Content->Id, 0, $lnNewStencilId );
					$this->StencilResource->Transformer = "{{block content}}\n{{{@@text@@}}}\n{{endblock}}";
					$this->StencilResource->Save();
				}
			}

			$this->MainResource->Save();
			
			foreach( $this->DatasetResources as $loDatasetResource )
			{
			    // Попытаемся найти ресурс шаблона
        		$laTmp = Resources::Enumerate( $this->StencilResources, $this->Content->Id, 0, -1, $loDatasetResource->Id );
        		
        		$loCurrentStencilResource = ( count($laTmp) > 0 ) ? $laTmp[ 0 ] : null;
        		$lnOldStencilId = ( $loCurrentStencilResource == null ) ? 0 : $loCurrentStencilResource->StencilId;
			    $lnNewStencilId = Net::PostResult( "stencil_id_".$loDatasetResource->Id );

			    if ( $lnOldStencilId != $lnNewStencilId)
			    {
				    if ($lnOldStencilId != 0)
					    $loCurrentStencilResource->Del();

				    if ($lnNewStencilId != 0)
				    {
					    $loResource = Resources::GetEmpty( $this->Content->Id, 0, $lnNewStencilId, $loDatasetResource->Id );
					    $loResource->Transformer = "{{block content}}\n{{{@@text@@}}}\n{{endblock}}";
					    $loResource->Save();
				    }
			    }
			}
		
           	Net::Redirect( Net::URL( Router::GetEffectivePath() ) );
			return true;
		}
		
		/* Добавление уровня иерархии */if ($psLevel1 == "append")
		{
			// Получим глубину иерархии для дополнения всяких строк
			$lnLevel = count( $this->Structure );
		
			// Создадим ПРИВЯЗАННЫЙ датасет
			// Заполним его данными из формы и ошкурим
			$loDataset = Datasets::GetEmpty();
				$loDataset->Name 		= "level_" . $lnLevel;
				$loDataset->Title 		= "Набор данных уровня " . ( $lnLevel + 1 );
				$loDataset->Description = "Набор данных для уровня " . ( $lnLevel + 1 ) . ". Содержит тэг и наименование объекта. Всё остальное — опционально.";
				$loDataset->IsBound 	= true;
				$loDataset->Save();
			
			// Добавим к нему тэг
			$loField = $loDataset->GetEmptyField();
				$loField->Title 	= "Тэг";
				$loField->Name 		= "tag";
				$loField->Class		= "DatasetFieldClassText";
				$loField->Fixed 	= true;
				$loField->Save();

			// ... и наименование
			$loField = $loDataset->GetEmptyField();
				$loField->Title 	= "Название";
				$loField->Name 		= "name";
				$loField->Class		= "DatasetFieldClassText";
				$loField->Fixed 	= true;
				$loField->Save();

			// Сохраним ресурс
			$loDatasetResource = Resources::GetEmpty( $this->Content->Id, $loDataset->Id );
				$loDatasetResource->ParentId	= ( $lnLevel == 0 ) ? $this->DatasetResource->Id : $this->Structure[ $lnLevel - 1 ]->Id;
				$loDatasetResource->Transformer = "{{result name @@text@@->Value}}";
				$loDatasetResource->Save();
		
           	Net::Redirect( Net::URL( Router::GetEffectivePath() ) );
			return true;
		} else 
		/* Удаление уровня иерархии */if ( $psLevel1 == "remove" )
		{
			// Получим глубину иерархии для дополнения всяких строк
			$lnLevel = count( $this->Structure );

		
           	Net::Redirect( Net::URL( Router::GetEffectivePath() ) );
			return true;
		} else 
		/* Сохранение названий уровней */if ( $psLevel1 == "save_level_names" )
		{
			for ($i=0; $i< count($this->Datasets); $i++ )
			{
				$lsNewLevelName = Net::PostResult("level_" . $i);
				if ( $lsNewLevelName !== null )
				{
					$this->Datasets[ $i ]->Title = $lsNewLevelName;
					$this->Datasets[ $i ]->Save();
				}
			}

           	Net::Redirect( Net::URL( Router::GetEffectivePath() ) );
			return true;
		}
	
		$laArgs = array();
		
		$laArgs [ "stencils" ] = Stencils::AllTopLevel(); 	// Передадим список шаблонов верхнего уровня
		$laArgs [ "value" ] = $this; 						// Передадим ссылку на себя
		$laArgs [ "content" ] = $this->Content; 			// Передадим ссылку на контент
		$laArgs [ "structure" ] = $this->Structure; 		// Типа иерархия
		$laArgs [ "datasets" ] = $this->Datasets;
		$laArgs [ "current_stencils" ] = $this->Stencils;

		// И вернём результат выполнения макроса
		Templates::Output( Templates::Code( Templates::Macro("assets/logic.hierarchy.config", $laArgs) ) );
		return true;
	}
		
	// Маршрутизация редактирования содержимого
	public function RouteEditing( )
	{
	    $laRouteArgs = func_get_args();
	    $lnLevel = 0;
	    $laEntitiesQueue = array();
	    $loEntity = null;
	    $loCurrentDatasetResource = null;
	    
		$laArgs = array();

		// Если не указан ресурс для редактирования - ну его в топку тогда.		
		if ($this->DatasetResource == null)
		{
    		$laArgs["value"] = $this->Content;
    		
			Templates::Output( Templates::Code( Templates::Macro("assets/logic.misconfigured", $laArgs) ) );
			return true;
		}
		
		// Пробежимся по параметрам и иерархии.
		foreach ($laRouteArgs as $lsArg)
		{
		    // Проверим на целочисленность
		    if ($lsArg == ($lsArg*1))
		    {
		        $loDatasetResource = $this->Structure[ $lnLevel ];
		        
		        if ($loDatasetResource == null)
		            break;
		        
		        $laEntities = $loDatasetResource->Entities( QB::E("id", "=", $lsArg) );
		        
		        if (count( $laEntities ) > 0)
		        {
		            $lnLevel ++;

	                $loCurrentDatasetResource = $loDatasetResource;
		            $loEntity = array_shift( $laEntities );

                    // Сохраним сущность в очередь
                    $laEntitiesQueue[] = $loEntity;
		        }else
		            break;
		    }
		}

        // Если мы так и не продвинулись по иерархии — будем править корневую сущность		
		if ($loEntity == null)
		{
		    $loCurrentDatasetResource = $this->DatasetResource;
    	    $laEntities = $loCurrentDatasetResource->Entities( null, null, QB::Limit(0, 1) );

		    // Получение сущностей. Вдруг протупили и забыли создать?				
		    if (count($laEntities) > 0)
			    $loEntity = array_shift( $laEntities );	
		    else
		    {
			    $loEntity = $loCurrentDatasetResource->GetEmpty();
			    $loEntity->Save();
		    }
		}

	    // Получим адаптер
	    $loAdapter = $loCurrentDatasetResource->StandaloneDataAdapter( $loEntity );

        // Сохранение?
        
        if (count($laRouteArgs) > 0)
        {
            $lsAction = $laRouteArgs[$lnLevel];

        	if ( ($lsAction == "save") && ( 
    	        ((Net::PostResult("id") == $this->Content->Id) && ($lnLevel == 0)) || 
    	        ((Net::PostResult("id") == $loEntity->Id) && ($lnLevel != 0)) 
        	))
       		{
		        foreach ($loAdapter->Fields as $lsName => $loField)
			        $loField->ReceiveEditFormPart( Net::PostResult() );
	
		        $loAdapter->Entity->Save();
		        
		        $loPath = Net::URL( Router::GetEffectivePath() );
		        
		        foreach ($laEntitiesQueue as $loQueuedEntity)
		            $loPath->Set( "./".$loQueuedEntity->Id."/" );

		        Net::Redirect( $loPath );
		        return true;
       		}

        	if ( ($lsAction == "del") && ($lnLevel != 0)) 
       		{
		        $loAdapter->Entity->Del();
		        
		        $loPath = Net::URL( Router::GetEffectivePath() );
		        
		        foreach ($laEntitiesQueue as $loQueuedEntity)
		            $loPath->Set( "./".$loQueuedEntity->Id."/" );
		            
		        $loPath->Set( "./../" );

		        Net::Redirect( $loPath );
		        return true;
       		}
        }

		if ($lnLevel<count($this->Structure))
		{
		    $loCurrentLevelDataset = $this->Datasets[$lnLevel];
		    $loCurrentLevelDatasetResource = $this->DatasetResources[$lnLevel];
            $loCurrentLevelEntity = $this->DatasetResources[$lnLevel]->GetEmpty();
		    $loCurrentLevelAdapter = $this->DatasetResources[$lnLevel]->StandaloneDataAdapter( $loCurrentLevelEntity );
		
    		$laArgs [ "current_level_dataset" ] = $loCurrentLevelDataset;
    		$laArgs [ "current_level_dataset_resource" ] = $loCurrentLevelDatasetResource;
    		$laArgs [ "current_level_adapter" ] = $loCurrentLevelAdapter;
    		
    		// Добавление для текущего уровня?

            if (count($laRouteArgs) > 0)
            {
                // Получим действие
                $psAction = $laRouteArgs[$lnLevel];

                // Соберём строку возврата
	            $loPath = Net::URL( Router::GetEffectivePath() );
	            foreach ($laEntitiesQueue as $loQueuedEntity)
	                $loPath->Set( "./".$loQueuedEntity->Id."/" );

           		/* Добавление нового элемента */ if (($psAction == "add") && ( (Net::PostResult("id") == $loCurrentLevelDatasetResource->Id) ) )
           		{
		            foreach ($loCurrentLevelAdapter->Fields as $lsName => $loField)
			            $loField->ReceiveEditFormPart( Net::PostResult() );

			        $loCurrentLevelEntity->ParentId = ($lnLevel == 0) ? 0 : $loEntity->Id;	
		            $loCurrentLevelEntity->Save();
		            
		            Net::Redirect( $loPath );
		            return true;
           		}
           		
            	/* Сдвиг вверх*/ if ($psAction == "up")
			    {
				    $loEntity->PrecedenceUp();
                	Net::Redirect( $loPath -> Set("./../") );
                	return true;
			    }
			    /* Сдвиг вниз */ if ($psAction == "down")
			    {
				    $loEntity->PrecedenceDown();
                	Net::Redirect( $loPath -> Set("./../") );
                	return true;
			    }           		
            }
            
            $laArgs [ "items" ] = $this->Items( $lnLevel, ($lnLevel == 0) ? 0 : $loEntity->Id );   		
    	}

		$laArgs [ "this" ] = $this; 						// Передадим ссылку на себя
		$laArgs [ "content" ] = $this->Content; 			// Передадим ссылку на контент
		$laArgs [ "content_adapter" ] = $loAdapter;
		$laArgs [ "structure" ] = $this->Structure; 		// Типа иерархия
		$laArgs [ "datasets" ] = $this->Datasets;
		$laArgs [ "stencils" ] = $this->Stencils;
		$laArgs [ "level" ] = $lnLevel;
		$laArgs [ "entities_queue" ] = $laEntitiesQueue;
		$laArgs [ "dataset" ] = Datasets::Get( $loCurrentDatasetResource->DatasetId );

		Templates::Output( Templates::Code( Templates::Macro("assets/logic.hierarchy.edit", $laArgs)) );
		return true;
	}
	
	public function RouteContentRendering()
	{
	    $laRouteArgs = func_get_args();
	    $lnLevel = 0;
	    $laEntitiesQueue = array();
	    $loEntity = null;
	    $loCurrentDatasetResource = null;
	    $loCurrentStencil = null;
	    $loCurrentStencilResource = null;
	    
		$laArgs = array();

		// Пробежимся по параметрам и иерархии.
		foreach ($laRouteArgs as $lsArg)
		{
	        $loDatasetResource = $this->Structure[ $lnLevel ];
	        $loStencilResource = $this->StencilResources[ $lnLevel ];	        
	        
	        if ($loDatasetResource == null)
	            return false;
	        
	        $laEntities = $loDatasetResource->Entities( QB::EOr( QB::E("id", "=", $lsArg), QB::E("tag", "=", $lsArg) ) );
	        
	        if (count( $laEntities ) > 0)
	        {
	            $lnLevel ++;

                $loCurrentDatasetResource = $loDatasetResource;
                $loCurrentStencilResource = $loStencilResource;
                $loCurrentStencil = $loStencilResource->Stencil;

	            $loEntity = array_shift( $laEntities );

                // Сохраним сущность в очередь
                $loAdapter = $loDatasetResource->StandaloneDataAdapter( $loEntity );
                $laEntitiesQueue[] = $loAdapter;
	        }else
	            return false;
		}

        // Если мы так и не продвинулись по иерархии — будем править корневую сущность		
		if ($loEntity == null)
		{
		    $loCurrentDatasetResource = $this->DatasetResource;
		    $loCurrentStencilResource = $this->StencilResource;
		    $loCurrentStencil = $this->Stencil;
    	    $laEntities = $loCurrentDatasetResource->Entities( null, null, QB::Limit(0, 1) );
		    $loEntity = array_shift( $laEntities );	
    	    $loAdapter = $loCurrentDatasetResource->StandaloneDataAdapter( $loEntity );
		}

		$laArgs [ "this" ] = $this->Content;
		$laArgs [ "logic" ] = $this;
        $laArgs [ "children" ] = ($lnLevel<count($this->Structure)) ? $this->Items( $lnLevel, ($lnLevel == 0) ? 0 : $loEntity->Id ) : array();
		$laArgs [ "item" ] = $loAdapter;
		$laArgs [ "levels" ] = $this->Structure;
		$laArgs [ "current_level" ] = $lnLevel;
		$laArgs [ "datasets" ] = $this->Datasets;
		$laArgs [ "stencils" ] = $this->Stencils;
		$laArgs [ "route" ] = $laEntitiesQueue;

		// Сначала пройдёмся трансформатором (преобразуем значения полей записи в что-то, пригодное для употребления блоками)
		$laResult = Templates::Results( $loCurrentDatasetResource->ThroughTransformer( $loEntity ) );
		// Теперь соберём аргументы
		$laWorkArgs = array_merge( $laResult , array( "this" => $this->Content ) );
		// Потом пройдёмся продуктором (соберём блоки)
		$laFinalResult = Templates::Blocks( $loCurrentStencilResource->ThroughProductor( $laWorkArgs ) );
		// А теперь соберём самые последние аргументы
		$laFinalArgs = array_merge( $laFinalResult , $laArgs );
		// И скормим блоки шаблону
		Templates::Output( Templates::Code( $loCurrentStencil->Render( $laFinalArgs ) ));
		
		return true;
	}
	
	/*Публичное апи*/
	
	public function Items( $pnLevel, $pnParentId=0 )
	{
	    // Получим уровень структуры
        $loDatasetResource = $this->Structure[ $pnLevel ];
        // Выберем сущности
		$lsOrder = array(
			QB::Field("p_id"),
			QB::Field("precedence")
		);
		$laEntities = $loDatasetResource->Entities( QB::E("p_id", "=", $pnParentId), $lsOrder );
        // Сконвертируем в адаптеры
        foreach ($laEntities as $lnKey=>$loValue)
            $laEntities[ $lnKey ] = $loDatasetResource->StandaloneDataAdapter( $loValue );        
        
        return $laEntities;	    
	}
}

Structure :: RegisterLogicClass( "StructureLogicClassHierarchy", "Иерархия" );
?>
