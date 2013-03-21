{{M l/top: "title"="Наборы данных" }}
{{M l/menu }}

<script>
	function doAddDataset(poForm)
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

	function doDelDataset(pnId)
	{
		if (confirm("Продолжить удаление?"))
			window.location.href = "./" + pnId + "/del/";
	}
</script>

<div id="dich-content">

	<div id="dich-breadcrumbs"><ul>
		<li>Наборы данных</li>
	</ul></div>
	<br />

		{{M l/e/fold_begin: "title"="Новый набор" }}
			<form name="submit" action="./add/" method="POST">
			<label>Название</label><input type="text" name="title" class="dich-input-text"/>
			<label>Идентификатор</label><input type="text" name="name" class="dich-input-text"/>
			<label>Описание</label><textarea name="description" class="dich-input-textarea"></textarea>
			<a href="#" onclick='doAddDataset(forms["submit"])' class="dich-input-button dich-input-submit">Добавить</a>
			</form>
		{{M l/e/fold_end }}
		
		<table class="dich-action-table">

			{{if count( %%values%% ) == 0 }}		
			<tr class="dich-missing-data-row">
				<td>Пусто</td>
			</tr>
			{{endif}}

			{{foreach %%value%% in %%values%%}}		
			<tr>
				<td>{{ %%value->Title%% }}{{if %%value->RefCount%% != 0 }}<span>жёстких ссылок: {{%%value->RefCount%%}}</span>{{endif}}</td>
				<td>
					{{if %%value->RefCount%% == 0 }}<a class="dich-input-button dich-input-button-del" href="#" onclick='doDelDataset("{{%%value->Id%%}}")'>{{else}}
					<a class="dich-input-button dich-input-button-disabled">{{endif}}Удалить</a>
					<a class="dich-input-button" href='./{{%%value->Id%%}}/'>Редактировать</a>
				</td>
			</tr>
			{{endfor}}
		
		</table>

</div>

{{M l/bottom }}
