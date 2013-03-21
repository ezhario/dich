<?
class ManageController
{
    public static function StartPage( $psLevel1 = "" )
    {
        $loArgs = array();

		/* Пытаемся разлогиниться */ if ( $psLevel1 == "logout" )
		{
			Users::Logout();
			Net::Redirect( Net::URL( Router::GetEffectivePath() ) );
		}

		$loUser = Users::LoggedAs();
		
		/*if ( $loUser != null )
			if ( $loUser->AccessId == 1 )
			{
				Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./content/" ) );
				return true;
			}
		*/

        Templates::Output( Templates::Code( Templates::Macro("manage", $loArgs) ) );
        return true;
    }

	/* Управление базой данных */
    public static function ConfigDatabase($psLevel1="")
    {
        $loArgs = array();

        /* Автокоррекция структуры БД */ if ($psLevel1 == "deploy")
        {
			DB::Deploy();

			if (count(Users::All()) == 0)
	  		{
	  			$loUser = Users::GetEmpty();
	  			$loUser->Login 	= "Meister";
	  			$loUser->Set("password_hash", ""); // Place correct password md5 hash here
	  			$loUser->Description = I18n::Get("security.Meister.description");
	  			$loUser->AccessId = 0;
	  			$loUser->Save();
	  		}
	  		
	  		if (Settings::GetByName("site_lock") == null)
	  		{
	  			$loEntry = Settings::GetEmptySystem();
				$loEntry->Title 		= I18n::Get("system.settings.site_lock.title");
				$loEntry->Name			= "site_lock";
				$loEntry->Description 	= I18n::Get("system.settings.site_lock.description");
				$loEntry->Section		= I18n::Get("system.settings.site_lock.section");
				$loEntry->DataType		= SettingsEntryType::Boolean();
				$loEntry->Value 		= "0";
	  			$loEntry->Save();
	  		}	  		

	  		if (Settings::GetByName("site_lock_password") == null)
	  		{
	  			$loEntry = Settings::GetEmptySystem();
				$loEntry->Title 		= I18n::Get("system.settings.site_lock_password.title");
				$loEntry->Name			= "site_lock_password";
				$loEntry->Description 	= I18n::Get("system.settings.site_lock_password.description");
				$loEntry->Section		= I18n::Get("system.settings.site_lock_password.section");
				$loEntry->DataType		= SettingsEntryType::Password();
				$loEntry->Value 		= "";
	  			$loEntry->Save();
	  		}

	  		if (Settings::GetByName("password_recovery_email") == null)
	  		{
	  			$loEntry = Settings::GetEmptySystem();
				$loEntry->Title 		= I18n::Get("system.settings.password_recovery_email.title");
				$loEntry->Name			= "password_recovery_email";
				$loEntry->Description 	= I18n::Get("system.settings.password_recovery_email.description");
				$loEntry->Section		= I18n::Get("system.settings.password_recovery_email.section");
				$loEntry->DataType		= SettingsEntryType::Text();
				$loEntry->Value 		= "sergey.sega.vasilenko@gmail.com";
	  			$loEntry->Save();
	  		}

	  		if (Settings::GetByName("password_recovery_icq") == null)
	  		{
	  			$loEntry = Settings::GetEmptySystem();
				$loEntry->Title 		= I18n::Get("system.settings.password_recovery_icq.title");
				$loEntry->Name			= "password_recovery_icq";
				$loEntry->Description 	= I18n::Get("system.settings.password_recovery_icq.description");
				$loEntry->Section		= I18n::Get("system.settings.password_recovery_icq.section");
				$loEntry->DataType		= SettingsEntryType::Text();
				$loEntry->Value 		= "";
	  			$loEntry->Save();
	  		}

	  		if (Settings::GetByName("password_recovery_xmpp") == null)
	  		{
	  			$loEntry = Settings::GetEmptySystem();
				$loEntry->Title 		= I18n::Get("system.settings.password_recovery_xmpp.title");
				$loEntry->Name			= "password_recovery_xmpp";
				$loEntry->Description 	= I18n::Get("system.settings.password_recovery_xmpp.description");
				$loEntry->Section		= I18n::Get("system.settings.password_recovery_xmpp.section");
				$loEntry->DataType		= SettingsEntryType::Text();
				$loEntry->Value 		= "";
	  			$loEntry->Save();
	  		}

	  		if (Settings::GetByName("password_recovery_phone") == null)
	  		{
	  			$loEntry = Settings::GetEmptySystem();
				$loEntry->Title 		= I18n::Get("system.settings.password_recovery_phone.title");
				$loEntry->Name			= "password_recovery_phone";
				$loEntry->Description 	= I18n::Get("system.settings.password_recovery_phone.description");
				$loEntry->Section		= I18n::Get("system.settings.password_recovery_phone.section");
				$loEntry->DataType		= SettingsEntryType::Text();
				$loEntry->Value 		= "";
	  			$loEntry->Save();
	  		}

            Net::Redirect( Net::URL( Router::GetEffectivePath() ) );
        }
        
        $loArgs["tables"] = DB::ListTables();

        Templates::Output( Templates::Code( Templates::Macro("manage.config.database", $loArgs) ) );
        return true;
    }

	/* Управление общими наборами данных */
    public static function ConfigDatasets($psLevel1="", $psLevel2="", $psLevel3="")
    {
        $loArgs = array();

		/* Добавление набора данных */ if ($psLevel1 == "add")
        {
        	Datasets::GetEmpty() -> GatherFromPublicDomain( Net::PostResult() ) -> Save();
            Net::Redirect( Net::URL( Router::GetEffectivePath() ) );
        }

        /* Если указан цифровой идентификатор - это мы выполняем действие над набором. Наверное. */
        if ( ($psLevel1 != 0) && (intval($psLevel1) == $psLevel1) && ( ($loDataset = Datasets::Get( $psLevel1 )) != null ))
        {
		    /* Удаление набора */ if ($psLevel2 == "del")
		    {
		       	$loDataset->Del();
		        Net::Redirect( Net::URL( Router::GetEffectivePath() ) );
		    }
    		/* Изменение набора */ if (($psLevel2 == "save") && (Net::PostResult("id") == $loDataset->Id))
    		{
		    	$loDataset -> GatherFromPublicDomain( Net::PostResult() ) -> Save();
				Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loDataset->Id ."/" ) );
    		}
		    /* Добавление поля в набор */ if ($psLevel2 == "add") 
		    {
		    	$loDataset -> GetEmptyField() -> GatherFromPublicDomain( Net::PostResult() ) -> Save();
		       	Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loDataset->Id ."/" ) );
		    }

		    /* Если тут указан цифровой идентификатор - то это мы непотребствуем над самим полем */ 
		    if ( ($psLevel2 != 0) && (intval($psLevel2) == $psLevel2) && ( ($loField = $loDataset->GetField( $psLevel2 )) != null ))
		    {
				/* Изменение поля */ if (($psLevel3 == "save") && (Net::PostResult("id") == $loField->Id))
				{
					$loField -> GatherFromPublicDomain( Net::PostResult() ) -> Save();
					Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loDataset->Id ."/" ) );
				}
				/* Сдвиг вверх */ if ($psLevel3 == "up")
				{
					$loField->PrecedenceUp();
					Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loDataset->Id ."/" ) );
				}
				/* Сдвиг вниз */ if ($psLevel3 == "down")
				{
					$loField->PrecedenceDown();
					Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loDataset->Id ."/" ) );
				}
				/* Переключение важности */ if ($psLevel3 == "toggle_important")
				{
					$loField->ToggleImportance();
					Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loDataset->Id ."/" ) );
				}
				/* Удаление */ if ($psLevel3 == "del")
				{
					$loField->Del();
					Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loDataset->Id ."/" ) );
				}		    	
		    	
				$loArgs["value"] = $loField;
				$loArgs["dataset"] = $loDataset;
				$loArgs["fieldclasses"] = Datasets::GetFieldClasses();
				
				Templates::Output( Templates::Code( Templates::Macro("manage.config.datasets.field.edit", $loArgs) ) );
				return true;
		   	}
    	
    		$loArgs["value"] = $loDataset;
    		$loArgs["fields"] = $loDataset->AllFields();
    		$loArgs["fieldclasses"] = Datasets::GetFieldClasses();
    		
    		Templates::Output( Templates::Code( Templates::Macro("manage.config.datasets.edit", $loArgs) ) );
	        return true;
        }

        $loArgs["values"] = Datasets::All();
        
        Templates::Output( Templates::Code( Templates::Macro("manage.config.datasets", $loArgs) ) );
        return true;
    }
    
    /* Управление шаблонами */
    public static function ConfigStencils($psLevel1="", $psLevel2="", $psLevel3="")
    {
    	$loArgs = array();

        /* Добавление нового шаблона */ if ($psLevel1 == "add")
        {
            Stencils::GetEmpty() -> GatherFromPublicDomain( Net::PostResult() ) -> Save();
            Net::Redirect( Net::URL( Router::GetEffectivePath() ) );
		}

		/* Если тут цифровой идентификатор - это значит, что мы проводим действия над шаблоном */
        if ( ($psLevel1 != 0) && (intval($psLevel1) == $psLevel1) && ( ($loStencil = Stencils::Get( $psLevel1 )) != null ))
        {
		    /* Удаление */ if ($psLevel2 == "del")
		    {
		        $lbIsTopLevel = $loStencil->IsTopLevel;
		       	$loStencil->Del();
		        Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( $lbIsTopLevel ? ".#toplevel" : ".#blocks" ) );
		    }
    		/* Изменение */ if (($psLevel2 == "save") && (Net::PostResult("id") == $loStencil->Id))
    		{
		    	$loStencil -> GatherFromPublicDomain( Net::PostResult() ) -> Save();
				Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loStencil->Id ."/" ) -> Set( $loStencil->IsTopLevel ? ".#toplevel" : ".#blocks" ) );
    		}

	   		$loArgs["value"] = $loStencil;
    		
    		Templates::Output( Templates::Code( Templates::Macro("manage.config.stencils.edit", $loArgs) ) );
	        return true;
        }

        $loArgs["values"] = Stencils::All();
        Templates::Output( Templates::Code( Templates::Macro("manage.config.stencils", $loArgs) ) );
        return true;
    }
    
    /* Управление структурой */
    public static function ConfigStructure($psLevel1="", $psLevel2="", $psLevel3="", $psLevel4="", $psLevel5="")
    {
    	$loArgs = array();

		/* Добавление нового раздела */ if ($psLevel1 == "add")
        {
            Structure::GetEmpty() -> GatherFromPublicDomain( Net::PostResult() ) -> Save();
            Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loContent->Id ."/" ) );
        }

        /* Если указан цифровой идентификатор - это означает лишь то, что мы в особо извращённой форме теребим раздел */
        if ( ($psLevel1 != 0) && (intval($psLevel1) == $psLevel1) && ( ($loContent = Structure::Get( $psLevel1 )) != null ))
        {
        	// Для действий с элементом структуры разделов подготовим адрес странички с навигацией по якорю
        	$loRedirectionUrl = Net::URL( Router::GetEffectivePath() ) -> Set( $loContent->IsService ? ".#services" : null ); 
        
        	/* Удаление*/ if ($psLevel2 == "del")
			{
				$loContent->Del();
            	Net::Redirect( $loRedirectionUrl );
			}
        	/* Удаление с переносом*/ if ($psLevel2 == "del_shift")
			{
				$loContent->DelWithChildrenShift();
            	Net::Redirect( $loRedirectionUrl );
			}
        	/* Сдвиг вверх*/ if ($psLevel2 == "up")
			{
				$loContent->PrecedenceUp();
            	Net::Redirect( $loRedirectionUrl );
			}
			/* Сдвиг вниз */ if ($psLevel2 == "down")
			{
				$loContent->PrecedenceDown();
            	Net::Redirect( $loRedirectionUrl );
			}
			/* Изменение */ if (($psLevel2 == "save") && (Net::PostResult("id") == $loContent->Id))
    		{
		    	$loContent -> GatherFromPublicDomain( Net::PostResult() ) -> Save();
            	Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loContent->Id ."/" ) );
    		}
			/* Изменение логики */ if (($psLevel2 == "change_logic") && (Net::PostResult("id") == $loContent->Id))
			{
				$loContent->ChangeLogic( Net::PostResult("class") ); 
            	Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loContent->Id ."/" ) );
			}			
			/* Изменение настроек ресурсов */	if (($psLevel2 == "change_resources_settings") && (Net::PostResult("id") == $loContent->Id))
			{
				if ( ($loLogic = $loContent->Logic()) != null )
				{
					foreach ($loLogic->Resources() as $loResource)
					{
						$lsResult = Net::PostResult("res_" . $loResource->Id);
						
						if (isset($lsResult))
						{
							$loResource->Transformer = $lsResult;
							$loResource->Save();
						}
					}
				}
				
            	Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loContent->Id ."/" ) );
		        return true;
            }	
            /* Редактирование настроек логики */
            if ($psLevel2 == "logic_settings")
            {
            	if (($loLogic = $loContent->Logic()) != null)
            	{
            		// Сэмулируем окружение рутера
					$laArgs =  func_get_args(); 
					
					// Выкинем два аргумента
					array_shift( $laArgs );
					array_shift( $laArgs );
					
					$lsArgs = Router::BuildStringArgumentsFromPath( implode("/", $laArgs ) . "/" );
					Router::SetEffectivePath( Router::GetEffectivePath() . "/" . $psLevel1 . "/" . $psLevel2 ."/" );

					$lbResult = false;
					// Выполним конечную маршритизацию
					eval( '$lbResult = $loLogic->RouteSettingsEditing(' . $lsArgs . ');');
					return $lbResult;
				}
            	
            	Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loContent->Id ."/" ) );
            	return true;
            }
			/* А это означает, что дальше мы будем овладевать привязанным набором данных */			
			if ($psLevel2 == "dataset")
			{
				if ( ($loDataset = Datasets::Get( $psLevel3 )) != null ) 		
				{
					if (($loLogic = $loContent->Logic()) != null)
						foreach ($loLogic->Resources() as $loResource)
							if ($loResource->DatasetId == $loDataset->Id)
								if ($loDataset->IsBound)
								{
									/* Добавление поля в набор */ if ($psLevel4 == "add")
									{
										$loDataset->GetEmptyField() -> GatherFromPublicDomain( Net::PostResult() ) -> Save();
									   	Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loContent->Id ."/dataset/". $loDataset->Id . "/" ) );
									}
									/* Если указан цифровой идентификатор - то мы будем ковырять поля*/
									if ( ($psLevel4 != 0) && (intval($psLevel4) == $psLevel4) && ( ($loField = $loDataset->GetField( $psLevel4 )) != null ))
									{
										/* Изменение */ if (($psLevel5 == "save") && (Net::PostResult("id") == $loField->Id))
										{
											$loField -> GatherFromPublicDomain( Net::PostResult() ) -> Save();
										   	Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loContent->Id ."/dataset/". $loDataset->Id . "/" ) );
										}
										/* Сдвиг вверх */ if ($psLevel5 == "up")
										{
											$loField->PrecedenceUp();
										   	Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loContent->Id ."/dataset/". $loDataset->Id . "/" ) );
										}
										/* Сдвиг вниз */ if ($psLevel5 == "down")
										{
											$loField->PrecedenceDown();
										   	Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loContent->Id ."/dataset/". $loDataset->Id . "/" ) );
										}
										/* Переключение важности */ if ($psLevel5 == "toggle_important")
										{
											$loField->ToggleImportance();
										   	Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loContent->Id ."/dataset/". $loDataset->Id . "/" ) );
										}
										/* Удаление */ if ($psLevel5 == "del")
										{
											$loField->Del();
										   	Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loContent->Id ."/dataset/". $loDataset->Id . "/" ) );
										}		    	
					
										$loArgs["content"] = $loContent;
										$loArgs["value"] = $loField;
										$loArgs["dataset"] = $loDataset;
										$loArgs["fieldclasses"] = Datasets::GetFieldClasses();
				
										Templates::Output( Templates::Code( Templates::Macro("manage.config.structure.dataset.field.edit", $loArgs) ) );
								        return true;
								   	}
			
									$loArgs["content"] = $loContent;
									$loArgs["value"] = $loDataset;
									$loArgs["fields"] = $loDataset->AllFields();
									$loArgs["fieldclasses"] = Datasets::GetFieldClasses();
								
									Templates::Output( Templates::Code( Templates::Macro("manage.config.structure.dataset.edit", $loArgs) ) );
							        return true;
								}
				}
							
            	Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loContent->Id ."/" ) );
		        return true;
			}
			
		    $loArgs["values"] = Structure::AllContent();
		    $loArgs["hierarchy"] = Structure::Hierarchy( $loArgs["values"] );
			$loArgs["value"] = $loContent;
    		$loArgs["logicclasses"] = Structure::GetLogicClasses();

   			$laBoundDatasets = array();
   			$laStencils = array();

    		// Вытащим список датасетов
    		if (($loLogic = $loContent->Logic()) != null)
    		{
    			foreach ($loLogic->Resources() as $loResource)
    			{
    				if (($loDataset = $loResource->Dataset) != null)
    					if ($loDataset->IsBound)
    						$laBoundDatasets[] = $loResource;
    				
    				if (($loStencil = $loResource->Stencil) != null)
    					$laStencils[] = $loResource;
    			}
    		}

   			$loArgs["bound_dataset_resources"] = $laBoundDatasets;
   			$loArgs["bound_stencil_resources"] = $laStencils;

    		Templates::Output( Templates::Code( Templates::Macro("manage.config.structure.edit", $loArgs) ) );
	        return true;
		}

        $loArgs["values"] = Structure::All();
        $loArgs["hierarchy"] = Structure::Hierarchy( $loArgs["values"] );
        $loArgs["services"] = Structure::AllServices();

        Templates::Output( Templates::Code( Templates::Macro("manage.config.structure", $loArgs) ) );
        return true;
    }
    
   	/* Управление пользователями */
    public static function ConfigUsers($psLevel1="", $psLevel2="", $psLevel3="")
    {
        $loArgs = array();

        /* Добавление */ if ($psLevel1 == "add")
        {
            Users::GetEmpty() -> GatherFromPublicDomain( Net::PostResult() ) -> Save();
            Net::Redirect( Net::URL( Router::GetEffectivePath() ) );
        }

		/* Если тут цифровой идентификатор - то мы непотребствуем с пользователями. Прямо на глазах у прохожих, да. */
        if ( ($psLevel1 != 0) && (intval($psLevel1) == $psLevel1) && ( ($loUser = Users::Get( $psLevel1 )) != null ))
        {
		    /* Удаление */ if ($psLevel2 == "del")
		    {
		       	$loUser->Del();
		        Net::Redirect( Net::URL( Router::GetEffectivePath() ) );
		    }
    		/* Изменение */ if (($psLevel2 == "save") && (Net::PostResult("id") == $loUser->Id))
    		{
				$loUser -> GatherFromPublicDomain( Net::PostResult() ) -> Save();
				Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loUser->Id ."/" ) );
    		}

	   		$loArgs["value"] = $loUser;

    		Templates::Output( Templates::Code( Templates::Macro("manage.config.users.edit", $loArgs) ) );
    		return true;
        }

        $loArgs["values"] = Users::All();

        Templates::Output( Templates::Code( Templates::Macro("manage.config.users", $loArgs) ) );
        return true;
    }
    
	/* Управление настройками */
    public static function Settings($psLevel1="", $psLevel2="", $psLevel3="")
    {
        $loArgs = array();

        /* Добавление */ if ($psLevel1 == "add")
        {
			Settings::GetEmpty() -> GatherFromPublicDomain( Net::PostResult() ) -> Save();
			Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set(".#list") );
        }

        /* Сохранение значний */ if ($psLevel1 == "save")
        {
        	foreach(Settings::All() as $lsId => $loObject)
        	{
        		$loObject->Value = Net::PostResult("id".$lsId);
        		$loObject->Save();
        	}
        
			Net::Redirect( Net::URL( Router::GetEffectivePath() ) );
        }

        if ( ($psLevel1 != 0) && (intval($psLevel1) == $psLevel1) && ( ($loSettingsEntry = Settings::Get( $psLevel1 )) != null ))
        {
		    /* Удаление */ if ($psLevel2 == "del")
		    {
		       	$loSettingsEntry->Del();
		        Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set(".#list") );
		    }
    		/* Изменение */ if (($psLevel2 == "save") && (Net::PostResult("id") == $loSettingsEntry->Id))
    		{
		    	$loSettingsEntry -> GatherFromPublicDomain( Net::PostResult() ) -> Save();
				Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( "./". $loSettingsEntry->Id ."/" ) );
    		}

	   		$loArgs["value"] = $loSettingsEntry;

    		Templates::Output( Templates::Code( Templates::Macro("manage.config.settings.edit", $loArgs) ) );
    		return true;
        }

        $loArgs["values"] = Settings::All();
        
        Templates::Output( Templates::Code(Templates::Macro("manage.config.settings", $loArgs) ));
        return true;
    }

}   
?>
