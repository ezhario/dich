{{M l/top: "title"="Структура сайта | Набор данных | Редактирование" }}
{{M l/menu }}

<script>
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
		<li><a href="../../../">Структура сайта</a></li>
		<li><a href="../../">«{{@@content@@->Title}}»</a></li>
		<li><span>{{@@value@@->Title}}</span></li>
	</ul></div>

	<br />

	{{M /l/e/tabs: "id"="dich-tabstop-1", "items"=array("dich-content-tab-all"="Все поля", "dich-content-tab-add"="Новое поле") }}
	
	<?/* Форма добавления поля */?>
	<div class="dich-tabs-tab" id="dich-content-tab-add">

		<form name="add" action="./add/" method="POST">
			<div class="column_2_0">
				<input type="hidden" name="d_id" value="{{ %%value->Id%% }}"/>
				<label>Название</label><input type="text" name="title" class="dich-input-text" value=""/>
				<label>Идентификатор</label><input type="text" name="name" class="dich-input-text" value=""/>
			</div>
			<div class="column_2_1">
				<label>Поле является обязательным</label><input type="checkbox" name="important" value="1" class="dich-input-checkbox" value="" />
				<label>Класс</label>
				<select name="class" class="dich-input-select">
					{{foreach %%fieldclass_title%% as %%fieldclass_name%% in %%fieldclasses%% }}
						<option value="{{%%fieldclass_name%%}}">{{%%fieldclass_title%%}}</option>
					{{endfor}}
				</select>
			</div>
			
			<a href="#" onclick='doAddDatasetField(forms["add"])' class="dich-action-button dich-input-submit">Добавить</a>
		</form>
	</div>

	<?/* Список полей */?>
	<div class="dich-tabs-tab" id="dich-content-tab-all">
		<table class="dich-action-table">
	
			{{if count( @@fields@@ ) == 0 }}		
			<tr class="dich-missing-data-row">
				<td>Полей в наборе нет</td>
			</tr>
			{{endif}} 

			{{foreach %%field%% in @@fields@@ }}		
			<tr>
				<td>
					<a href='./{{%%field%%->Id}}/' title="Редактировать">{{ %%field%%->Title }}</a> <span>{{ %%fieldclasses%%["%%field->Class%%"] }}, {{ %%field->Name%% }}</span>{{if %%field%%->Important == 1 }}<span>Обязательное</span>{{endif}}
				</td>
				<td>
					{{if !%%field%%->Fixed}}<a class="dich-action-button dich-action-delete" href="#" onclick='doDelDatasetField("{{%%field%%->Id}}")' title="Удалить"><span></span></a>{{else}}<span class="dich-action-button dich-action-button-right"></span>{{endif}}
					<a class="dich-action-button dich-action-edit" href='./{{%%field%%->Id}}/' title="Редактировать"><span></span></a>
					{{if %%field%%->Precedence != (count(@@fields@@)-1) }}<a class="dich-action-button dich-action-down" href='./{{%%field%%->Id}}/down/' title="Ниже"><span></span></a>{{else}}<span class="dich-action-button dich-action-button-right"></span>{{endif}}
					{{if %%field%%->Precedence != 0 }}<a class="dich-action-button dich-action-up" href='./{{%%field%%->Id}}/up/' title="Выше"><span></span></a>{{else}}<span class="dich-action-button dich-action-button-right"></span>{{endif}}
					<a class="dich-action-button dich-action-importance {{if %%field%%->Important == 1 }}dich-action-button-highlighted{{endif}}" href='./{{%%field%%->Id}}/toggle_important/' title="{{if %%field%%->Important == 1 }}Выключить обязательность{{else}}Включить обязательность{{endif}}"><span></span></a>
				</td>
			</tr>
			{{endfor}}
		
		</table>
	</div>

</div>

{{M l/bottom }}
