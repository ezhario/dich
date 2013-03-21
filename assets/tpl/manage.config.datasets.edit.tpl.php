{{M l/top: "title"="Наборы данных | Редактирование" }}
{{M l/menu }}

<script>
	function doSaveDataset(poForm)
	{
		if ( dichTextIsEmpty( poForm.elements["title"].value )) {
			alert("Обязательно укажите название");
			return false;
		}
		
		if ( !dichTextIsIdentifier( poForm.elements["name"].value )) {
			alert("Идентификатор должен начинаться с латинской буквы и состоять из латинских букв или цифр");
			return false;
		}
		
		poForm.submit();
	}
	function doAddDatasetField(poForm)
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
	function doDelDatasetField(pnId)
	{
		if (confirm("Продолжить удаление?"))
			window.location.href = "./" + pnId + "/del/";
	}
</script>

<div id="dich-content">

	<?/* Шапка */?>
	<div id="dich-breadcrumbs"><ul>
		<li><a href="..">Наборы данных</a></li>
		<li>Редактирование</li>
	</ul></div>
	
	<? %%fold_title%% = "Набор №" . @@value->Id@@ ?>
		
	<?/* Основная форма редактирования */?>
	{{M l/e/fold_begin: "title"="%%fold_title%%", "expanded"="true", "nothumb"="true", "calm"="true" }}
		<form name="save" action="./save/" method="POST">
			<input type="hidden" name="id" value="{{ %%value->Id%% }}"/>
			<label>Название</label><input type="text" name="title" class="dich-input-text" value="{{ %%value->Title%% }}"/>
			<label>Идентификатор</label><input type="text" name="name" class="dich-input-text" value="{{ %%value->Name%% }}"/>
			<label>Описание</label><textarea name="description" class="dich-input-textarea dich-input-textarea-small">{{ %%value->Description%% }}</textarea>
			<a href="#" onclick='doSaveDataset(forms["save"])' class="dich-input-button dich-input-submit">Сохранить</a>
		</form>
	{{M l/e/fold_end }}
	
	<br />
	
	<?/* Форма добавления поля */?>
	{{M l/e/fold_begin: "title"="Новое поле" }}
		<form name="add" action="./add/" method="POST">
			<input type="hidden" name="d_id" value="{{ %%value->Id%% }}"/>
			<label>Название</label><input type="text" name="title" class="dich-input-text" value=""/>
			<label>Идентификатор</label><input type="text" name="name" class="dich-input-text" value=""/>
			<label>Класс</label>
				<select name="class" class="dich-input-select">
					{{foreach %%fieldclass_title%% as %%fieldclass_name%% in %%fieldclasses%% }}
						<option value="{{%%fieldclass_name%%}}">{{%%fieldclass_title%%}}</option>
					{{endfor}}
				</select>
			<label>Обязательность</label><input type="checkbox" name="important" value="1" class="dich-input-checkbox" value=""/>
			<a href="#" onclick='doAddDatasetField(forms["add"])' class="dich-input-button dich-input-submit">Добавить</a>
		</form>
	{{M l/e/fold_end }}

	<?/* Список полей */?>
	<table class="dich-action-table">
	
		{{if count( @@fields@@ ) == 0 }}		
		<tr class="dich-missing-data-row">
			<td>Полей в наборе нет</td>
		</tr>
		{{endif}} 

		{{foreach %%field%% in @@fields@@ }}		
		<tr>
			<td>
				{{ %%field->Title%% }} <span>{{ %%fieldclasses%%["%%field->Class%%"] }}, {{ %%field->Name%% }}</span>
			</td>
			<td>
				<a class="dich-input-button dich-input-button-del" href="#" onclick='doDelDatasetField("{{%%field->Id%%}}")'>Удалить</a>
				<a class="dich-input-button" href='./{{%%field->Id%%}}/'>Редактировать</a>
				{{if %%field->Precedence%% == (count(@@fields@@)-1) }}<a class="dich-input-button dich-input-button-disabled">&darr;</a>{{else}}<a class="dich-input-button" href='./{{%%field->Id%%}}/down/'>&darr;</a>{{endif}}
				{{if %%field->Precedence%% == 0 }}<a class="dich-input-button dich-input-button-disabled">&uarr;</a>{{else}}<a class="dich-input-button" href='./{{%%field->Id%%}}/up/'>&uarr;</a>{{endif}}
				<a class="dich-input-button{{if %%field->Important%% == 1 }} dich-input-button-ok{{endif}}" href='./{{%%field->Id%%}}/toggle_important/'>!</a>
			</td>
		</tr>
		{{endfor}}
		
	</table>
	
	{{if %%value->RefCount%% != 0 }}
	<br />
	<br />
	* На этот набор данных есть ссылки, поэтому система немного ограничит вас в возможностях по его редактированию.
	{{endif}}

</div>

{{M l/bottom }}
