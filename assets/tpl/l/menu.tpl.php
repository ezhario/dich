<div id="dich-header">
	<div id="dich-title">{{ Config::$Site["title"] }}<a href="/" target="_blank">открыть в новом окне</a></div>
	<div class="dich-main-menu">
		<ul>
			<li><a href="/manage/" class="dich-main-menu-item-dich">DICH-board</a></li>
			
			{{M l/e/main_menu_item: "href"="/manage/content/","title"="Наполнение" }}
			{{M l/e/main_menu_item: "href"="/manage/settings/","title"="Настройки" }}

			{{if Users::LoggedAs()->AccessId == 0}}
			{{M l/e/main_menu_item: "href"="/manage/structure/","title"="Структура" }}
			{{M l/e/main_menu_item: "href"="/manage/stencils/","title"="Шаблоны" }}
			{{M l/e/main_menu_item: "href"="/manage/db/","title"="БД" }}
			{{M l/e/main_menu_item: "href"="/manage/users/","title"="Пользователи" }}
			{{endif}}

			{{M l/e/main_menu_item: "href"="/manage/logout/","title"="Выход","warning"="true" }}

		</ul>
	</div>
</div> 
