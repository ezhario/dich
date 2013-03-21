{{M l/top: "title"="Главная страница" }}
{{M l/menu }}

	<div id="dich-content">
		<div class="column_2_0">
			<? 
				%%logged_as%% = Users::LoggedAs(); 
				%%user_name%% = "";
				
				if ( trim(%%logged_as%%->First) == "" )
					%%user_name%% = %%logged_as%%->Login;
				else
				{
					%%user_name%% = 
						trim( %%logged_as%%->First ) . " " .
						trim( %%logged_as%%->Second ) . " " .
						trim( %%logged_as%%->Last );
				}
			?>
			<div class="dich-section">{{ %%user_name%% }}</div>
			Добро пожаловать в систему управления сайтом «<a target="_blank" href="/">{{ Config::$Site["title"] }}</a>».
			{{if %%logged_as%%->AccessId == 0 }}
			<br /><br />В настоящее время вы работаете с привилегиями суперпользователя, поэтому — убедительная к вам просьба быть внимательными, осмотрительными, дальновидными и очень аккуратными. В противном случае вы можете что-нибудь поломать, а это всегда грустно и плохо.
			{{else}}
			{{endif}}
		</div>
		<div class="column_2_1">
			{{if Config::InstallScriptExists() }}
			<div class="dich-section">Внимание!</div>
			Безопасность сайта под угрозой. Инсталляционный скрипт /assets/INSTALL.php не удалён. Пожалуйста, в целях уменьшения количества потенциальных угроз удалите этот файл с рабочего хостинга.
			{{endif}}
		</div>
	</div>

{{M l/bottom }}
