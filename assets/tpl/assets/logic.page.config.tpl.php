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
	
		<label>Шаблон</label>
		<select name="stencil_id" class="dich-input-select">
				<option value="0" {{if 0 == %%value->StencilId%% }} selected{{endif}}>Нет</option>
				{{foreach %%stencil_item%% as %%stencil_id%% in @@stencils@@ }}
					<option value="{{ %%stencil_id%% }}"{{if %%stencil_id%% == @@value->StencilId@@ }} selected{{endif}}>{{ %%stencil_item->Title%% }}</option>
				{{endfor}}
		</select>

		<a href="#" onclick='doSaveSettings(forms["change_settings"])' class="dich-action-button dich-input-submit">Сохранить</a>

	</form>
		
</div>

{{M l/bottom }}
