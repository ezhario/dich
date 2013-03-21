<?
class ContentController
{
    public static function EditContent($psLevel1="", $psLevel2="", $psLevel3="")
    {
        $loArgs = array();

		// Если мы выбрали редактирование элемента
		if ( ($psLevel1 != 0) && (intval($psLevel1) == $psLevel1) && ( ($loContent = Structure::Get( $psLevel1 )) != null ))
		{
			/* Изменение метаданных */ if ( $psLevel2 == "metadata" )
			{
				if ( ($psLevel3 == "save") && (Net::PostResult("id") == $loContent->Id))
				{
					$loContent->Meta = Net::PostResult("meta");
					$loContent->Save();
				
	            	Net::Redirect( Net::URL( Router::GetEffectivePath() . "/" . $psLevel1 . "/" ));
				}
			}					
		
			// Если у выбранного элемента есть логика — то будем её редактировать
			if (($loContent->Class != "") && ($loContent->Class != null))
		    {
				$loLogic = $loContent->Logic();
				if ($loLogic != null)
					if ( $loLogic->IsEditingSupported() )
					{
						$laArgs =  func_get_args(); 
						array_shift( $laArgs );

						$lsArgs = Router::BuildStringArgumentsFromPath( implode("/", $laArgs ) . "/" );

						// Сэмулируем окружение рутера
						Router::SetEffectivePath( Router::GetEffectivePath() . "/" . $psLevel1 . "/" );
	
						$lbResult = false;
						// Выполним конечную маршритизацию
						eval( '$lbResult = $loLogic->RouteEditing(' . $lsArgs . ');');
				
						return $lbResult;
					}
		    }

			Net::Redirect( Net::URL( Router::GetEffectivePath() ) );
	        return true;
		}
		    
        $loArgs["values"] = Structure::All();
        $loArgs["hierarchy"] = Structure::Hierarchy( $loArgs["values"] );
        $loArgs["services"] = Structure::AllServices();
        
        print( Templates::Code(Templates::Macro("manage.content", $loArgs) ));
        return true;
    }
    
    protected static function WalkHierarchy( $paAll, $paHierarchy, $pnId, $psPath)
    {
    	foreach ($paHierarchy[ $pnId ] as $lnId)
    	{
    		if (($loContent = $paAll[$lnId]) != null)
    		{
    			$lsPath = $psPath . $loContent->Name . "/";
    			
    			if ($loContent->Class != "")
					Router::AddRoute( $lsPath, "Structure::Get($lnId)->Logic()->RouteContentRendering");
    		
	    		ContentController::WalkHierarchy($paAll, $paHierarchy, $lnId, $lsPath);
	    	}
    	}
    }
    
    public static function RegisterContentRoutes()
    {
    	$laAll = Structure::AllEnabled();
    	$laHierarchy = Structure::Hierarchy($laAll);
    	
    	ContentController::WalkHierarchy( $laAll, $laHierarchy, 0, "" );
    }
}
?>
