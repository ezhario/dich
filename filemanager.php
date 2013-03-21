<?
	// Минимальная часть ядра
	include_once("./assets/inc/lib/config.inc.php");		// Конфигурация
	include_once("./assets/inc/lib/net.inc.php");			// Сетевые дела
	include_once("./assets/inc/lib/db.inc.php");			// База данных
	include_once("./assets/inc/lib/files.inc.php");		// Файлы
	include_once("./assets/inc/obj/settings.inc.php");		// Настройки
	include_once("./assets/inc/obj/users.inc.php");		// Пользователи

	// Инициализируем сессию
	$lsSid = Net::PostResult("sid");
	Users::Init( $lsSid );
	// Проверим пользователя
	$loUser = Users::LoggedAs();
	// Если не залогинены - 404
	if ($loUser == null) 
		Net::Report404();
	
	// Кэширование? нет пути.
	Net::ForceInvalidateCaching();
	
	$lsAction = Net::PostResult("a");
	$lsRoot = Config::$System["documentroot"];

	// Листинг файлов	
	if ($lsAction == "ls")
	{
		/* в JSON отдаётся список записей вида: n - имя, p - путь, s - размер, e - расширение, d - признак каталога, ro - признак "только для чтения", i - изображение, a - права */
		Net::Output( JSON::Encode( 
			Files::GetDirectoryListing( 
				Net::PostResult("p")
			) 
		) );
	}

	// Создать папку
	if ($lsAction == "mkdir")
	{
		Net::Output( JSON::Encode( 
			Files::MakeDirectory( 
				Net::PostResult("p")
			) 
		) );
	}

	// Иначе примем файлы
	if ( count( $loUploadedFiles = Net::UploadedFiles() ) > 0 )
	{
		$loFilesInformation = array();
		$lsStoreTo = Net::PostResult("p");

		foreach ( Net::UploadedFiles() as $k=>$v )
		{
			if ( $v['error'] === UPLOAD_ERR_OK )
			{
				$loInfo = new UploadedFileInfo( $v );
				$loStoredInfo = Files::Store( $loInfo, $lsStoreTo );
				$loFilesInformation[] = array("p"=>$loStoredInfo->URL);
			}
		}

		/* в JSON отдаётся список записей вида: p - путь" */
		Net::Output( JSON::Encode( 
			$loFilesInformation
		) );
	}
	
?>
