<?
	// Ядро
	include_once("./assets/inc/lib/config.inc.php");		// Конфигурация
	include_once("./assets/inc/lib/net.inc.php");			// Сетевые дела
	include_once("./assets/inc/lib/router.inc.php");		// УРЛ-рутер 
	include_once("./assets/inc/lib/log.inc.php");			// Журнал
	include_once("./assets/inc/lib/db.inc.php");			// База данных
	include_once("./assets/inc/lib/cache.inc.php");			// Кэш
	include_once("./assets/inc/lib/files.inc.php");			// Файлы
	include_once("./assets/inc/lib/templates.inc.php");		// Шаблоны
	
	// Системная мидлварь
	include_once("./assets/inc/obj/settings.inc.php");		// Настройки
	include_once("./assets/inc/obj/users.inc.php");			// Пользователи
	Users::Init();
	
	// Мидлварь - логика сущностей
	include_once("./assets/inc/obj/datasets.inc.php");		// Наборы данных
	include_once("./assets/inc/obj/stencils.inc.php");		// Пользовательские шаблоны 
	include_once("./assets/inc/obj/structure.inc.php");		// Структура
	
	// Фронтэнды - привязки УРЛ-рутинга
	include_once("./assets/inc/roadmap.inc.php");

	// Инициализация рутинга
	Router::Route( Net::GetResult("route") );
?>
