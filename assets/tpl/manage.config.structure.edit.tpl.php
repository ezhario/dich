{{M l/top: "title"="Структура сайта | Редактирование" }}
{{M l/menu }}

<script>
	function doSaveContent(poForm)
	{
		if ( dichTextIsEmpty( poForm.elements["title"].value )) {
			alert("Обязательно укажите название");
			return false;
		}

		if ( 
			(!dichTextIsEmpty( poForm.elements["name"].value )) && 
			(!dichTextIsURLFriendly( poForm.elements["name"].value ))
		)
		{
			alert("Идентификатор должен начинаться с латинской буквы и состоять из латинских букв или цифр");
			return false;
		}
		
		poForm.submit();
	}
	function doChangeContentLogic(poForm)
	{
		if (confirm("Сохранить логику?"))
			poForm.submit();
	}
	function doChangeContentLogicSettings(poForm)
	{
		if (confirm("Сохранить настройки логики?"))
			if (doAllowChangeContentLogicSettings(poForm))
				poForm.submit();	
	}
	function doChangeContentLogicViewSettings(poForm)
	{
		if (confirm("Сохранить настройки вида?"))
			poForm.submit();	
	}	
</script>

<div id="dich-content">

	<div id="dich-breadcrumbs"><ul>
		<li><a href="..{{if @@value@@->IsService}}#services{{endif}}">Структура сайта</a></li>
		<li><span>«{{@@value@@->Title}}»</span></li>
	</ul></div>

	<br />

	<? 
		%%logic%% = @@value@@->Logic();
		%%there_are_bound_dataset_resources%% = ( count(@@bound_dataset_resources@@) > 0 );

		%%tabs%% = array();
		
		if (%%logic%% != null)
			if (%%there_are_bound_dataset_resources%%)
				%%tabs%%["dich-content-tab-bound-resources"] = "Наборы данных";
		
		%%tabs%%["dich-content-tab-edit"] = "Основные настройки";
		%%tabs%%["dich-content-tab-logic"] = "Логика";

		if (%%logic%% != null)
		{
			%%tabs%%["dich-content-tab-logic-view-settings"] = "Настройки отображения";
		}
	?>
	
	{{M /l/e/tabs: "id"="dich-tabstop-2","items"=%%tabs%% }}
	
		<?/* Основная форма редактирования */?>
		<div id="dich-content-tab-edit" class="dich-tabs-tab">
		
			<form name="save" action="./save/" method="POST">

				<input type="hidden" name="id" value="{{ %%value%%->Id }}"/>
				<input type="hidden" name="is_service" value="{{ %%value%%->IsService }}"/>
				
				{{if %%value%%->IsService}}
				<input type="hidden" name="menu_entry" value="{{ %%value%%->MenuEntry }}" />
				<input type="hidden" name="p_id" value="0" />
				{{endif}}
				
				<div class="column_2_0">
					<label>Название</label><input type="text" name="title" class="dich-input-text" value="{{ %%value%%->Title }}"/>
					<label>Идентификатор</label><input type="text" name="name" class="dich-input-text" value="{{ %%value%%->Name }}"/>
					{{if %%value%%->IsContent}}<label>Виден в меню как</label><input type="text" name="menu_entry" class="dich-input-text" value="{{ %%value%%->MenuEntry }}"/>{{endif}}
				</div>
				<div class="column_2_1">
					{{if %%value%%->IsContent}}
					<label id="p_id_label">Родительский элемент</label>
					<select name="p_id" class="dich-input-select">
							<option value="0"{{if 0 == %%value%%->ParentId }} selected{{endif}}>Нет</option>
							{{foreach %%value_item%% as %%value_id%% in @@values@@ }}
								<option value="{{ %%value_id%% }}"{{if %%value_id%% == %%value%%->ParentId }} selected{{endif}}>{{ %%value_item%%->Title }}</option>
							{{endfor}}
					</select>
					{{endif}}
					<label>Выключен</label><input type="checkbox" name="disabled" class="dich-input-checkbox" value="1" {{if %%value->Disabled%% == "1" }} checked {{endif}} />
					<label>Показывается при построении меню</label><input type="checkbox" name="is_menu_entry" value="1" {{if %%value->IsMenuEntry%% == "1" }} checked {{endif}} class="dich-input-checkbox" />
				</div>
		
				<a href="#" onclick='doSaveContent(forms["save"])' class="dich-action-button dich-input-submit">Сохранить</a>
			</form>
		</div>

		<?/* Форма смены логики */?>
		<div id="dich-content-tab-logic" class="dich-tabs-tab">
			<form name="change_logic" action="./change_logic/" method="POST">

			<input type="hidden" name="id" value="{{ %%value->Id%% }}"/>
	
			{{if %%value%%->Class !="" }}
			<div class="column_2_0">
				У выбранной логики раздела могут быть различные настройки, как то шаблоны для отрисовки страниц, 
				параметры структуры или вид группировки. Их можно изменить, но будьте внимательны — изменения могут повредить или безвозвратно изменить
				данные раздела.
				<br />
				<a href="./logic_settings/" class="dich-action-button dich-input-submit">Изменить настройки логики</a>
			</div>
			<div class="column_2_1">
			{{endif}}
				<label>Текущая логика работы</label>
					<select name="class" class="dich-input-select">
							<option value=""{{if "" == %%value%%->Class }} selected{{endif}}>Отсутствует</option>
							{{foreach %%class_text%% as %%class_name%% in @@logicclasses@@ }}
								<option value="{{ %%class_name%% }}"{{if %%class_name%% == %%value%%->Class }} selected{{endif}}>{{ %%class_text%% }}</option>
							{{endfor}}
					</select>

				<a href="#" onclick='doChangeContentLogic(forms["change_logic"])' class="dich-action-button dich-input-submit">Сохранить выбранный вид логики</a>
			{{if %%value%%->Class !="" }}</div>{{endif}}

			</form>
		</div>

		<?/* Если привязана логика */?>
		{{if %%logic%% != null }}

			<?/* Форма настроек вида */?>
			<div id="dich-content-tab-logic-view-settings" class="dich-tabs-tab">
			
				<form name="change_logic_view_settings" action="./change_resources_settings/" method="POST">

				<input type="hidden" name="id" value="{{ %%value->Id%% }}"/>
				
				<?/* Трансформаторы */?>
				{{if count(@@bound_dataset_resources@@) > 0}}		
				
					<div class="dich-section">Трансформаторы</div>
					<div class="dich-notification">
						Трансформаторы используются для преобразования полей набора данных перед дальнейшим использованием в продукторах. <br />
						В качестве @\@…@\@-параметров трансформатору передаются все поля набора данных. <br />
						В трансформаторе должны быть указаны одна или несколько конструкций вида {\{result <имя> <значение>}\}. 
						Эти результаты под указанными именами попадут в продуктор в качестве параметров.
					</div>	

					{{foreach %%resource%% in @@bound_dataset_resources@@}}
						<? %%dataset_elem_name%% = "res_" . %%resource%%->Id ?>
						<label>{{ %%resource%%->Dataset->Title }}</label><textarea name="{{%%dataset_elem_name%%}}" id="{{%%dataset_elem_name%%}}" class="dich-input-textarea dich-input-textarea-big">{{ %%resource%%->Transformer }}</textarea>
						{{M l/e/codemirror: "id"="%%dataset_elem_name%%" }}
					{{endfor}}
				{{endif}}

				<?/* Продукторы */?>
				{{if count(@@bound_stencil_resources@@) > 0}}		
				
					<div class="dich-section">Продукторы</div>
					<div class="dich-notification">
						Продукторы используются для формирования из приготовленных трансформаторами записей готовых блоков для вставки в конечные шаблоны. <br />
						Блоки размечаются с помощью конструкции {\{block <имя>}\}. <br />
						Внутри блоков могут использоваться параметры (именованные так, как это указано в соответствующем трансформаторе).
					</div>	

					{{foreach %%resource%% in @@bound_stencil_resources@@}}
						<? %%dataset_elem_name%% = "res_" . %%resource%%->Id ?>
						<label>{{ %%resource%%->Stencil->Title }}</label><textarea name="{{%%dataset_elem_name%%}}" id="{{%%dataset_elem_name%%}}" class="dich-input-textarea dich-input-textarea-big">{{ %%resource%%->Transformer }}</textarea>
						{{M l/e/codemirror: "id"="%%dataset_elem_name%%" }}
					{{endfor}}
				{{endif}}

				<a href="#" onclick='doChangeContentLogicViewSettings(forms["change_logic_view_settings"])' class="dich-action-button dich-input-submit">Сохранить</a>

				</form>

				<?/* Помощь */?>
				{{if (count(@@bound_dataset_resources@@) > 0) || ( count(@@bound_stencil_resources@@) > 0) }}
					{{M stuff/stencils.syntax.annotation: "caption"="Показать помощь по синтаксису шаблонизатора", "id"="dich-templates-help", "button_id"="dich-templates-help-button" }}
				{{endif}}
			</div>

			<?/* Список привязанных датасетов */?>
			{{if count(@@bound_dataset_resources@@) > 0}}		
			<div id="dich-content-tab-bound-resources" class="dich-tabs-tab">
				<table class="dich-action-table">
					{{foreach %%resource%% in @@bound_dataset_resources@@}}
					<? %%dataset%% = %%resource%%->Dataset ?>
					<tr>
						<td><a href="./dataset/{{%%dataset%%->Id}}/">{{ %%dataset%%->Title }}</a></td>
						<td><a class="dich-action-button dich-action-edit" href='./dataset/{{%%dataset%%->Id}}/' title="Редактировать"><span></span></a></td>
					</tr>
					{{endfor}}
				</table>
			</div>
			{{endif}}

		{{endif}}
		
</div>

{{M l/bottom }}
