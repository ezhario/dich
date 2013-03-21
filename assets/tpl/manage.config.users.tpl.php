{{M l/top: "title"="Пользователи" }}
{{M l/menu }}

<script>
	function doAddUser(poForm)
	{
		poForm.submit();
	}

	function doDelUser(pnId)
	{
		if (confirm("Продолжить удаление?"))
			window.location.href = "./" + pnId + "/del/";
	}
</script>

<div id="dich-content">

	{{M /l/e/tabs: "id"="dich-tabstop-2","items"=array("dich-content-tab-all"="Все пользователи", "dich-content-tab-add"="Новый пользователь") }}
	
	<div id="dich-content-tab-add" class="dich-tabs-tab">
		
			<form name="submit" action="./add/" method="POST">
				<div class="column_2_0">
					<label>Логин</label><input type="text" name="login" class="dich-input-text"/>
					<label>Пароль</label><input type="password" name="password" class="dich-input-text"/>
					<label>Пароль (повторите)</label><input type="password" name="password2" class="dich-input-text"/>
				</div>
				<div class="column_2_1">
					<label>Фамилия</label><input type="text" name="last" class="dich-input-text"/>
					<label>Имя</label><input type="text" name="first" class="dich-input-text"/>
					<label>Отчество</label><input type="text" name="second" class="dich-input-text"/>
					<label>Телефон</label><input type="text" name="phone" class="dich-input-text"/>
					<label>Описание</label><textarea name="description" class="dich-input-textarea dich-input-textarea-small"></textarea>
					<label>Уровень доступа</label><input type="text" name="access_id" class="dich-input-text" value="1"/>
				</div>
				<a href="#" onclick='doAddUser(forms["submit"])' class="dich-action-button dich-input-submit">Добавить</a>
			</form>
		
	</div>

	<div id="dich-content-tab-all" class="dich-tabs-tab">

		<table class="dich-action-table">

			{{if count( @@values@@ ) == 0 }}		
			<tr class="dich-missing-data-row">
				<td>Пусто</td>
			</tr>
			{{endif}}

			{{foreach %%value%% in @@values@@}}		
			<tr>
				<td>
					<a href='./{{%%value%%->Id}}/' title="Редактировать">{{ %%value->Login%% }}</a>
					<span>{{ %%value%%->Name }}</span>
					{{if %%value%%->Phone != "" }}<span>{{%%value%%->Phone}}</span>{{endif}}
				</td>
				<td>
					<a class="dich-action-button dich-action-delete" href="#" onclick='doDelUser("{{%%value%%->Id}}")' title="Удалить"><span></span></a>
					<a class="dich-action-button dich-action-edit" href='./{{%%value%%->Id}}/' title="Редактировать"><span></span></a>
				</td>
			</tr>
			{{endfor}}
	
		</table>
		
	</div>

</div>

{{M l/bottom }}
