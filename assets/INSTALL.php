<?
	// Ядро
	include_once("./inc/lib/config.inc.php");		// Конфигурация
	include_once("./inc/lib/net.inc.php");			// Сетевые дела
	include_once("./inc/lib/router.inc.php");		// УРЛ-рутер 
	include_once("./inc/lib/log.inc.php");			// Журнал
	include_once("./inc/lib/db.inc.php");			// База данных
	include_once("./inc/lib/cache.inc.php");			// Кэш
	include_once("./inc/lib/files.inc.php");			// Файлы
	include_once("./inc/lib/templates.inc.php");		// Шаблоны
	
	// Системная мидлварь
	include_once("./inc/obj/settings.inc.php");		// Настройки
	include_once("./inc/obj/users.inc.php");			// Пользователи
	Users::Init();
	
	// Мидлварь - логика сущностей
	include_once("./inc/obj/datasets.inc.php");		// Наборы данных
	include_once("./inc/obj/stencils.inc.php");		// Пользовательские шаблоны 
	include_once("./inc/obj/structure.inc.php");		// Структура
	
	// Фронтэнды - привязки УРЛ-рутинга
	include_once("./inc/roadmap.inc.php");

	// Деплой БД
	Router::SetEffectivePath("/manage/");
	ManageController::ConfigDatabase("deploy");
?>
