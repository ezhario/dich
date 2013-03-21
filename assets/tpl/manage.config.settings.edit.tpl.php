{{M l/top: "title"="Настройки | Редактирование" }}
{{M l/menu }}

<? 
	%%fold_title%% = "Настройка №" . @@value->Id@@;
	%%is_user_entry%% = @@value@@->IsUserSettingsEntry;
?>

<script>
	function doSaveSettingsEntry(poForm)
	{
		if ( dichTextIsEmpty( poForm.elements["title"].value )) {
			alert("Обязательно укажите человекочитаемое название");
			return false;
		}
		
		{{if %%is_user_entry%% }}
			if ( !dichTextIsIdentifier( poForm.elements["name"].value )) {
				alert("Идентификатор должен начинаться с латинской буквы и состоять из латинских букв или цифр");
				return false;
			}	
		{{endif}}
			
		poForm.submit();
	}
</script>

<div id="dich-content">

	<?/* Шапка */?>
	<div id="dich-breadcrumbs"><ul>
		<li><a href="..#list">Все настройки</a></li>
		<li><span>{{ %%fold_title%% }}</span></li>
	</ul></div>
	
	<?/* Основная форма редактирования */?>
	<br />
	
	{{if !%%is_user_entry%% }}
		<h3>Эта настройка — системная, либо привязанная к какой-либо системной сущности. Ваши возможности по её редактированию будут ограничены.</h3>
	{{endif}}

	<form name="save" action="./save/" method="POST">
		<input type="hidden" name="id" value="{{ @@value@@->Id }}"/>
	
		<div class="column_2_0">
			<label>Наименование</label><input type="text" name="title" class="dich-input-text" value="{{ @@value@@->Title }}"/>
			<label>Идентификатор</label><input type="text" name="name" class="dich-input-text" value="{{ @@value@@->Name }}"{{if !%%is_user_entry%% }} DISABLED{{endif}}/>
			<label>Секция</label><input type="text" name="section" class="dich-input-text" value="{{ @@value@@->Section }}"/>
			<label>Тип данных</label><select id="data_type" name="data_type" class="dich-input-select"{{if !%%is_user_entry%% }} DISABLED{{endif}}>
					{{foreach %%datatypevalue%% as %%datatypekey%% in Settings::Types() }}
						<option value="{{ %%datatypekey%% }}"{{if %%datatypekey%% == @@value@@->DataType }} selected{{endif}}>{{ %%datatypevalue%% }}</option>
					{{endfor}}
			</select>
		</div>
		<div class="column_2_1">
			<label>Описание</label><textarea name="description" class="dich-input-textarea dich-input-textarea-small">{{ @@value@@->Description }}</textarea>
			<label id="min_value_label">Минимальное значение</label><input id="min_value" type="text" name="min_value" class="dich-input-text" value="{{ @@value@@->MinValue }}"/>
			<label id="max_value_label">Максимальное значение</label><input id="max_value" type="text" name="max_value" class="dich-input-text" value="{{ @@value@@->MaxValue }}"/>
		</div>
		
		<a href="#" onclick='doSaveSettingsEntry(forms["save"])' class="dich-action-button dich-input-submit">Сохранить</a>
	</form>
</div>

<script>
	$("#data_type").bind("change", function(){
	
		var lbShowMinAndMaxValues = (this.value == "{{ SettingsEntryType::Number() }}");
		
		$("#min_value_label")
		.add("#max_value_label")
		.add("#min_value")
		.add("#max_value")
			.toggle( lbShowMinAndMaxValues );

	});
	$("#data_type").trigger("change");
</script>

{{M l/bottom }}
