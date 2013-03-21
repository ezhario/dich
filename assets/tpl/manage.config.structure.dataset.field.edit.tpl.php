{{M l/top: "title"="Структура сайта | Набор данных | Редактирование" }}
{{M l/menu }}

<script>
	function doSaveDatasetField(poForm)
	{
		if ( dichTextIsEmpty( poForm.elements["title"].value )) {
			alert("Обязательно укажите название");
			return false;
		}
		
		if ( !dichTextIsIdentifier( poForm.elements["name"].value )) {
			alert("Идентификатор должен начинаться с латинской буквы и состоять из латинских букв или цифр");
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
	<div id="dich-breadcrumbs"><ul>
		<li><a href="../../../../">Структура сайта</a></li>
		<li><a href="../../../">«{{@@content->Title@@}}»</a></li>
		<li><a href="..">{{@@dataset@@->Title}}</a></li>
		<li><span>Редактирование поля</span></li>
	</ul></div>

	<br />

	{{if @@value@@->Fixed }}
		<h3>Это поле было создано системой автоматически. Ваши возможности по его редактированию будут ограничены.</h3>
	{{endif}}
	
	<?/* Основная форма редактирования */?>
	<form name="save" action="./save/" method="POST">

		<input type="hidden" name="id" value="{{ %%value%%->Id }}"/>
	
		<div class="column_2_0">
			<input type="hidden" name="d_id" value="{{ %%value->Id%% }}"/>
			<label>Название</label><input type="text" name="title" class="dich-input-text" value="{{ %%value%%->Title }}"/>
			<label>Идентификатор</label><input type="text" name="name" class="dich-input-text" value="{{ %%value%%->Name }}" {{if @@value@@->Fixed}}DISABLED{{endif}}/>
		</div>
		<div class="column_2_1">
			<label>Поле является обязательным</label><input type="checkbox" name="important" class="dich-input-checkbox" value="1" {{if %%value%%->Important == "1" }} checked {{endif}} />
			<label>Класс</label>
			<select name="class" class="dich-input-select">
				{{foreach %%fieldclass_title%% as %%fieldclass_name%% in %%fieldclasses%% }}
					<option value="{{%%fieldclass_name%%}}"{{if %%fieldclass_name%% == %%value%%->Class }} selected{{endif}}>{{%%fieldclass_title%%}}</option>
				{{endfor}}
			</select>
		</div>
		
		<a href="#" onclick='doSaveDatasetField(forms["save"])' class="dich-action-button dich-input-submit">Сохранить</a>
	</form>

</div>

{{M l/bottom }}
