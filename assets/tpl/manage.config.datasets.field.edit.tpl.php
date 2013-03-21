{{M l/top: "title"="Наборы данных | Редактирование" }}
{{M l/menu }}

<script>
	function doSaveDatasetField(poForm)
	{
		if ( dichTextIsEmpty( poForm.elements["title"].value )) {
			alert("Обязательно укажите название");
			return false;
		}
		
		if ( !dichTextIsIdentifier( poForm.elements["name"].value )) {
			alert("Идентификатор начинаться с латинской буквы и состоять из латинских букв или цифр");
			return false;
		}

		if ( 
			(poForm.elements["name"].value == "id")		||
			(poForm.elements["name"].value == "c_id")	||
			(poForm.elements["name"].value == "d_id")	
		) {
			alert("Выберите другой идентификатор, пожалуйста");
			return false;
		}

		poForm.submit();
	}
</script>

<div id="dich-content">

	<?/* Шапка */?>
	<? %%fold_title%% = "Набор «" . @@dataset->Title@@ . "»" ?>
	
	<div id="dich-breadcrumbs"><ul>
		<li><a href="../../">Наборы данных</a></li>
		<li><a href="..">{{%%fold_title%%}}</a></li>
		<li>Редактирование</li>
	</ul></div>

	<? %%fold_title%% = "Поле №" . @@value->Id@@ ?>
	
	<?/* Основная форма редактирования */?>
	{{M l/e/fold_begin: "title"="%%fold_title%%", "expanded"="true", "nothumb"="true", "calm"="true" }}
		<form name="save" action="./save/" method="POST">
			<input type="hidden" name="id" value="{{ %%value->Id%% }}"/>
			<label>Название</label><input type="text" name="title" class="dich-input-text" value="{{ %%value->Title%% }}"/>
			<label>Идентификатор</label><input type="text" name="name" class="dich-input-text" value="{{ %%value->Name%% }}"/>
			<label>Класс</label>
				<select name="class" class="dich-input-select">
					{{foreach %%fieldclass_title%% as %%fieldclass_name%% in %%fieldclasses%% }}
						<option value="{{%%fieldclass_name%%}}"{{if %%fieldclass_name%% == %%value->Class%% }} selected{{endif}}>{{%%fieldclass_title%%}}</option>
					{{endfor}}
				</select>
			<label>Обязательность</label><input type="checkbox" name="important" class="dich-input-checkbox" value="1" {{if %%value->Important%% == "1" }} checked {{endif}} />
			<a href="#" onclick='doSaveDatasetField(forms["save"])' class="dich-input-button dich-input-submit">Сохранить</a>
		</form>
	{{M l/e/fold_end }}

	{{if %%dataset->RefCount%% != 0 }}
	<br />
	<br />
	* На этот набор данных есть ссылки, поэтому система немного ограничит вас в возможностях по редактированию поля.
	{{endif}}
	
</div>

{{M l/bottom }}
