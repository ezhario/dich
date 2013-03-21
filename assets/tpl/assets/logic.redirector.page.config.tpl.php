{{M l/top: "title"="Структура сайта | Редактирование | Настройки логики" }}
{{M l/menu }}

<script>
	function doSaveSettings(poForm)
	{
		if (confirm("Сохранить настройки логики?"))
			if (doAllowSaveSettings(poForm))
				poForm.submit();	
	}
	function doAllowSaveSettings( poForm )
	{
		return true;
	}
</script>

<div id="dich-content">

	<div id="dich-breadcrumbs"><ul>
		<li><a href="../../{{if @@content@@->IsService}}#services{{endif}}">Структура сайта</a></li>
		<li><a href="../">«{{@@content@@->Title}}»</a></li>
		<li><span>Настройки логики</span></li>
	</ul></div>

	<br />

	<?/* Форма настроек вида */?>
	<form name="change_settings" action="./save/" method="POST">

		<input type="hidden" name="id" value="{{ @@content@@->Id }}"/>
	
		<label>Ссылка для перенаправления</label>
		<input type="text" name="raw_redirect_link" class="dich-input-text" value="{{ @@value@@->RedirectionDestination }}" />

		<a href="#" onclick='doSaveSettings(forms["change_settings"])' class="dich-action-button dich-input-submit">Сохранить</a>

	</form>
		
</div>
