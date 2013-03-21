<?
class StructureLogicClassPage extends StructureLogic
{
	/* Стандартные методы класса логики */	
	
	public function StructureLogicClassPage( $poContent )
	{
		StructureLogic::StructureLogic( $poContent );
	}

	protected function OnInit( $paResources )
	{
		$laDatasetResources = Resources::Enumerate( $paResources, $this->Content->Id, -1, 0, 0 );
		$laStencilResources = Resources::Enumerate( $paResources, $this->Content->Id, 0, -1, 0 );
		
		$this->DatasetResource = (count($laDatasetResources) > 0) ? $laDatasetResources[0] : null;
		$this->StencilResource = (count($laStencilResources) > 0) ? $laStencilResources[0] : null;
	}

	protected function OnDeploy( )
	{
		/* Создадим ПРИВЯЗАННЫЙ датасет */
    	$loDataset = Datasets::GetEmpty();
    	$loDataset->Name 		= "_default";
    	$loDataset->Title 		= "Набор данных страницы";
    	$loDataset->Description = "Стандартный набор данных страницы, создаваемый автоматически. Уникален для каждой страницы (раздела с типом логики «Простая страница»)";
    	$loDataset->IsBound 	= true;
    	$loDataset->Save();
    	
    	/* Добавим к нему поле по умолчанию */
    	$loDefaultField = $loDataset->GetEmptyField();
    	$loDefaultField->Title 		= "Текст";
    	$loDefaultField->Name 		= "text";
    	$loDefaultField->Class		= "DatasetFieldClassDHTML";
    	$loDefaultField->Fixed 		= true;
    	$loDefaultField->Save();

		/* Сохраним ресурс */
		$loDatasetResource = Resources::GetEmpty( $this->Content->Id, $loDataset->Id);
		$loDatasetResource->Transformer = "{{result text @@text@@->Value}}";
		$loDatasetResource->Save();
		
		/* Добавим одну пустую строку на этот контент */
		$loEntity = $loDatasetResource->GetEmpty();
		$loEntity->Save();
		
		$this->DatasetResource = $loDatasetResource;

		/* Ссылку на шаблон уберём */
		$_oStencilResource = null;
	}

	protected function OnUnlink( )
	{
		// Подчистим за собой
		if ($this->DatasetId != 0)
			$this->DatasetResource->Del();
			
		// И здесь тоже подчистим
		if ($this->StencilId != 0)
			$this->StencilResource->Del();
	}
	
	/****************************************
		Специфичные методы, аксессоры и поля
	*****************************************/

	protected $_oDatasetResource = null;
	protected $_oDataset = null;

	protected $_oStencilResource = null;
	protected $_oStencil = null;
	
	protected function get_DatasetResource() { return $this->_oDatasetResource; }
	protected function get_StencilResource() { return $this->_oStencilResource; }

	protected function get_Dataset() { return $this->_oDataset; }
	protected function get_Stencil() { return $this->_oStencil; }

	protected function get_DatasetId() { return $this->_oDataset == null ? 0 : $this->_oDataset->Id; }
	protected function get_StencilId() { return $this->_oStencil == null ? 0 : $this->_oStencil->Id; }
	
	protected function set_DatasetResource($poValue) 
	{ 
		$this->_oDatasetResource = $poValue;
		$this->_oDataset = ($poValue == null) ? null : Datasets::Get( $poValue->DatasetId );  
	}
	
	protected function set_StencilResource($poValue) 
	{ 
		$this->_oStencilResource = $poValue; 
		$this->_oStencil = ($poValue == null) ? null : Stencils::Get( $poValue->StencilId );  
	}

	/****************************************
		Шрутизация 
	*****************************************/

	public function RouteSettingsEditing( $psLevel1 )
	{
		if ($psLevel1 == "save")
		{
			$lnOldStencilId = $this->StencilId;
			$lnNewStencilId = Net::PostResult("stencil_id");

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
		
           	Net::Redirect( Net::URL( Router::GetEffectivePath() ) );
			return true;
		}
	
		// Заполним аргументы макроса
		$laArgs = array( "stencils" => Stencils::AllTopLevel(), "value" => $this, "content" => $this->Content );

		// И вернём результат выполнения макроса
		Templates::Output( Templates::Code( Templates::Macro("assets/logic.page.config", $laArgs) ) );
		return true;
	}
	
	public function RouteEditing( $psAction )
	{
		$laArgs = array();

		$laArgs["value"] = $this->Content;

		// Если не указан ресурс для редактирования - ну его в топку тогда.		
		if ($this->DatasetResource == null)
		{
			Templates::Output( Templates::Code( Templates::Macro("assets/logic.misconfigured", $laArgs) ) );
			return true;
		}

		$loEntity = null;
		$laEntities = $this->DatasetResource->Entities( null, null, QB::Limit(0, 1) );

		// Получение сущностей. Вдруг протупили и забыли создать?				
		if (count($laEntities) > 0)
			$loEntity = array_shift( $laEntities );	
		else
		{
			$loEntity = $this->DatasetResource->GetEmpty();
			$loEntity->Save();
		}

		// Получим адаптер
		$loAdapter = $this->DatasetResource->StandaloneDataAdapter( $loEntity );
		
		// Сохранение?
   		if (($psAction == "save") && (Net::PostResult("id") == $this->Content->Id))
   		{
			foreach ($loAdapter->Fields as $lsName => $loField)
				$loField->ReceiveEditFormPart( Net::PostResult() );
		
			$loEntity->Save();
	
			Net::Redirect( Net::URL( Router::GetEffectivePath() ) );
			return true;
   		}

		$laArgs["content"] = $loAdapter;
		
		Templates::Output( Templates::Code( Templates::Macro("assets/logic.page.edit", $laArgs)) );
		return true;
	}
	
	public function RouteContentRendering()
	{
		// Обычной странице не нужна локальная навигация. усеницы, червиё и мракобесие это всё.
		if (func_num_args() != 0)
			return false;
			
		if ( ($this->Stencil != null) && ($this->Dataset != null) )
		{
			if (count($laEntities = $this->DatasetResource->Entities( null, null, QB::Limit(0, 1) )) > 0)
			{
				if ( ($loEntity = array_shift( $laEntities )) != null)
				{
					// Сначала пройдёмся трансформатором (преобразуем значения полей записи в что-то, пригодное для употребления блоками)
					$laResult = Templates::Results( $this->DatasetResource->ThroughTransformer( $loEntity ) );
					
					// Теперь соберём аргументы
					$laArgs = array_merge( $laResult , array( "this" => $this->Content ) );
					// Потом пройдёмся продуктором (соберём блоки)
					$laFinalResult = Templates::Blocks( $this->StencilResource->ThroughProductor( $laArgs ) );
					
					// А теперь соберём самые последние аргументы
					$laFinalArgs = array_merge( $laFinalResult , array( "this" => $this->Content ) );
					
					// И скормим блоки шаблону
					Templates::Output( Templates::Code( $this->Stencil->Render( $laFinalArgs ) ));
					
					return true;
				}
			}
		}
	
		return false;
	}
	
	public function Page()
	{
	    // Достанем сущность страницы
        $loDatasetResource = $this->DatasetResource;
		$laEntities = $loDatasetResource->Entities( null, null, QB::Limit(0,1) );

        // Прокинем в адаптер
        return $loDatasetResource->StandaloneDataAdapter( array_shift($laEntities) );        
	}

}

Structure :: RegisterLogicClass( "StructureLogicClassPage", "Простая страница" );
?>
