{{M l/top: "title"="Вход в систему", "safe"=true }}

<div id="dich-content">

	<div id="dich-login-form-container">
	
		{{M /l/e/tabs: "id"="dich-tabstop-login","items"=array("dich-content-tab-login"="Представьтесь", "dich-content-tab-forgotten"="Забыли пароль?") }}
		
		<div class="dich-login-form-body">
		
			<div class="dich-tabs-tab" id="dich-content-tab-login">
				<form name="save" action="./login/" method="POST">

					<div><label>Логин</label><input type="text" name="login" class="dich-input-text" /></div>
					<div><label>Пароль</label><input type="password" name="password" class="dich-input-text" id="passwordInput" /></div>
					<div><a href="#" onclick='doLogin(forms["save"])' class="dich-action-button dich-action-button-login">Дальше...</a></div>
				
				</form>
			</div>
	
			<div class="dich-tabs-tab" id="dich-content-tab-forgotten">
				<? 
					%%rs_email%% = Settings::GetByName("password_recovery_email");
					%%rs_icq%% = Settings::GetByName("password_recovery_icq");
					%%rs_xmpp%% = Settings::GetByName("password_recovery_xmpp");
					%%rs_phone%% = Settings::GetByName("password_recovery_phone");
				?>
				{{if trim(%%rs_email%%->Value)!==""}}
				<div class="dich-forgotten-password-email">
					<h1>E-Mail</h1>
					<a href="mailto:{{{ %%rs_email%%->Value }}}">{{ %%rs_email%%->Value }}</a>
				</div>
				{{endif}}
				{{if trim(%%rs_icq%%->Value)!==""}}
				<div class="dich-forgotten-password-icq">
					<h1>ICQ</h1>
					<span>{{ %%rs_icq%%->Value }}</span>
				</div>
				{{endif}}
				{{if trim(%%rs_xmpp%%->Value)!==""}}
				<div class="dich-forgotten-password-xmpp">
					<h1>Jabber/XMPP</h1>
					<span>{{ %%rs_xmpp%%->Value }}</span>
				</div>
				{{endif}}
				{{if trim(%%rs_phone%%->Value)!==""}}
				<div class="dich-forgotten-password-phone">
					<h1>Телефон</h1>
					<span>{{ %%rs_phone%%->Value }}</span>
				</div>
				{{endif}}
			</div>

		</div>
	</div>

	<script>
		function doLogin( poForm ){ poForm.submit(); }
		$(document).ready( function() {
			$("#passwordInput").keypress( function (e) {
				keyCode = e.which ? e.which : e.keyCode;
				if (keyCode == 13)
				{
					$(document.forms["save"]).submit();
				}
			});
		});
	</script>
	
</div>

{{M l/bottom }}
