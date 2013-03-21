{{M l/top: "title"="Пользователи | Редактирование" }}
{{M l/menu }}

<script>
	function doSaveUser(poForm)
	{
		poForm.submit();
	}
</script>

<div id="dich-content">

	<?/* Шапка */?>
	<div id="dich-breadcrumbs"><ul>
		<li><a href="..">Все пользователи</a></li>
		<li><span>Редактирование</span></li>
	</ul></div>
	
	<br clear="both" />
	
	<?/* Основная форма редактирования */?>
	<form name="save" action="./save/" method="POST">
		<input type="hidden" name="id" value="{{ @@value@@->Id }}"/>

		<div class="column_2_0">
			<label>Логин</label><input type="text" name="login" class="dich-input-text" value="{{ @@value@@->Login }}"/>
			<label>Пароль</label><input type="password" name="password" class="dich-input-text" value=""/>
			<label>Пароль (повторите)</label><input type="password" name="password2" class="dich-input-text" value=""/>
		</div>
		
		<div class="column_2_1">
			<label>Фамилия</label><input type="text" name="last" class="dich-input-text" value="{{ @@value@@->Last }}"/>
			<label>Имя</label><input type="text" name="first" class="dich-input-text" value="{{ @@value@@->First }}"/>
			<label>Отчество</label><input type="text" name="second" class="dich-input-text" value="{{ @@value@@->Second }}"/>
			<label>Телефон</label><input type="text" name="phone" class="dich-input-text" value="{{ @@value@@->Phone }}"/>
			<label>Описание</label><textarea name="description" class="dich-input-textarea dich-input-textarea-small">{{ @@value@@->Description }}</textarea>
			<label>Уровень доступа</label><input type="text" name="access_id" class="dich-input-text" value="{{ @@value@@->AccessId }}"/>
		</div>
		
		<a href="#" onclick='doSaveUser(forms["save"])' class="dich-action-button dich-input-submit">Сохранить</a>
	</form>
	
</div>

{{M l/bottom }}
