<?
	include_once("routes/errors.inc.php");
	include_once("routes/manage.inc.php");
	include_once("routes/content.inc.php");
	include_once("routes/login.inc.php");
	
	// Маршруты по умолчанию
	Router::PushFailsafeRoute("ErrorsController::Show404");                     // Ошибка 404 - самый распоследний вариант
	Router::PushFailsafeRoute("ErrorsController::ResizeImageOnDemand");         // Ресайз запрашиваемого изображения под нужный размер
	Router::PushFailsafeRoute("ErrorsController::ShowSiteLock");                // Проверка блокировки сайта
	// Маршрутизация контента
	ContentController::RegisterContentRoutes();
	// Карта маршрутов для админки
	//               Маршрут               Контроллер                           Только после авторизации
	Router::AddRoute("/manage/",           "LoginController::LoginPage");
	Router::AddRoute("/manage/",           "ManageController::StartPage",       true);
	Router::AddRoute("/manage/stencils/",  "ManageController::ConfigStencils",  true);
	Router::AddRoute("/manage/structure/", "ManageController::ConfigStructure", true);
	Router::AddRoute("/manage/settings/",  "ManageController::Settings",        true);
	Router::AddRoute("/manage/users/",     "ManageController::ConfigUsers",     true);
	Router::AddRoute("/manage/db/",        "ManageController::ConfigDatabase",  true);
	// Редактирование контента
	Router::AddRoute("/manage/content/",   "ContentController::EditContent",    true);
?>
