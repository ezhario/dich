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
	function doAppendLevel( pnContentId )
	{
		if (confirm("Добавить? Уверены?"))
			window.location.href = "./append/";
	}
	function doRemoveLevel( pnContentId )
	{
		if (confirm("Убрать? Уверены?"))
			window.location.href = "./remove/";
	}
	function doSaveNames(poForm)
	{
		if (confirm("Сохранить названия уровней?"))
				poForm.submit();	
	}
</script>

<div id="dich-content">

	<div id="dich-breadcrumbs"><ul>
		<li><a href="../../{{if @@content@@->IsService}}#services{{endif}}">Структура сайта</a></li>
		<li><a href="../">«{{@@content@@->Title}}»</a></li>
		<li><span>Настройки логики</span></li>
	</ul></div>

	<br />
	
	{{M /l/e/tabs: "id"="dich-tabstops","items"=array("dich-hierarchy-settings-tab-all"="Структура", "dich-hierarchy-settings-tab-settings"="Общие настройки"), "anchor_navigation"=array("structure"="dich-hierarchy-settings-tab-all") }}

	<?/* Форма настроек вида */?>
	<div id="dich-hierarchy-settings-tab-settings" class="dich-tabs-tab">
		<form name="change_settings" action="./save/" method="POST">

			<input type="hidden" name="id" value="{{ @@content@@->Id }}"/>
	
			<label>Шаблон корневой страницы</label>
			<select name="stencil_id" class="dich-input-select">
					<option value="0" {{if 0 == %%value->StencilId%% }} selected{{endif}}>Нет</option>
					{{foreach %%stencil_item%% as %%stencil_id%% in @@stencils@@ }}
						<option value="{{ %%stencil_id%% }}"{{if %%stencil_id%% == @@value->StencilId@@ }} selected{{endif}}>{{ %%stencil_item->Title%% }}</option>
					{{endfor}}
			</select>

            {{if count( @@structure@@ ) != 0 }}
            <br /><h3>Шаблоны разделов каталога</h3>
			{{endif}}

			<? %%level%% = 0; ?>
			{{foreach %%category%% in @@structure@@}}		
			<label>Шаблон раздела «{{ @@datasets@@[%%level%%]->Title }}»</label>
			<select name="stencil_id_{{{ %%category%%->Id }}}" class="dich-input-select">
					<option value="0" {{if 0 == %%category->StencilId%% }} selected{{endif}}>Нет</option>
					{{foreach %%stencil_item%% as %%stencil_id%% in @@stencils@@ }}
						<option value="{{ %%stencil_id%% }}"{{if %%stencil_id%% == @@current_stencils[%%category%%->Id]->Id@@ }} selected{{endif}}>{{ %%stencil_item->Title%% }}</option>
					{{endfor}}
			</select>
			<? %%level%%++; ?>
			{{endfor}}
				
			<a href="#" onclick='doSaveSettings(forms["change_settings"])' class="dich-action-button dich-input-submit">Сохранить</a>

		</form>
	</div>

	<?/* Структура */?>
	<div id="dich-hierarchy-settings-tab-all" class="dich-tabs-tab">

		<form name="level_names" action="./save_level_names/" method="POST">
			<input type="hidden" name="id" value="{{ @@content@@->Id }}"/>

			<table class="dich-action-table">

				{{if count( @@structure@@ ) == 0 }}		
				<tr class="dich-missing-data-row">
					<td>Пусто</td>
				</tr>
				{{endif}}

				<? %%level%% = 0; ?>			
			
				{{foreach %%category%% in @@structure@@}}		
				<tr>
					<td>
						<input type="text" name="level_{{{ %%level%% }}}" class="dich-input-text" value="{{ @@datasets@@[%%level%%]->Title }}" />
					</td>
				</tr>
				<? %%level%%++; ?>
				{{endfor}}

			</table>

			{{if count( @@structure@@ ) != 0 }}		
				<a href="#" onclick='doRemoveLevel("{{ @@content@@->Id }}")' class="dich-action-button dich-input-submit dich-input-button-in-line dich-action-delete"><span></span>Убрать последний уровень</a>
			{{endif}}
			<a href="#" onclick='doAppendLevel("{{ @@content@@->Id }}")' class="dich-action-button dich-input-submit dich-input-button-in-line">Добавить ещё уровень</a>
			<a href="#" onclick='doSaveNames(forms["level_names"])' class="dich-action-button dich-input-submit dich-input-button-in-line">Сохранить названия уровней</a>
		</form>
	</div>


</div>

{{M l/bottom }}
