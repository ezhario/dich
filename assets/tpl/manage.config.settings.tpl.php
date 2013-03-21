{{M l/top: "title"="Настройки" }}
{{M l/menu }}

<script>
	function doAddSettingsEntry(poForm)
	{
		if ( dichTextIsEmpty( poForm.elements["title"].value )) {
			alert("Обязательно укажите человекочитаемое название");
			return false;
		}
		
		if ( !dichTextIsIdentifier( poForm.elements["name"].value )) {
			alert("Идентификатор должен начинаться с латинской буквы и состоять из латинских букв или цифр");
			return false;
		}	
		poForm.submit();
	}

	function doSaveSettings(poForm)
	{
		poForm.submit();
	}

	function doDelSettingsEntry(pnId)
	{
		if (confirm("Продолжить удаление?"))
			window.location.href = "./" + pnId + "/del/";
	}
</script>

<div id="dich-content">

	{{if Users::LoggedAs()->AccessId == 0}}
	{{M /l/e/tabs: "id"="dich-tabstop-2","items"=array("dich-content-tab-all"="Значения", "dich-content-tab-list"="Все настройки", "dich-content-tab-add"="Новая настройка"), "anchor_navigation"=array("list"="dich-content-tab-list") }}
	{{endif}}
	
	<div id="dich-content-tab-all" class="dich-tabs-tab{{if Users::LoggedAs()->AccessId == 1}} dich-tabs-current{{endif}}">

		<form name="save" action="./save/" method="POST">
			<?
				%%counter%% = 0; 
				%%section_name%% = "";
			?>
			{{foreach %%value%% in @@values@@}}
			
			{{if %%section_name%% != %%value%%->Section }}
			<? 
				%%section_name%% = %%value%%->Section; 
				%%counter%% = 0;
				
				%%section_disp_name%% = (%%section_name%%[0] == "_")
					? I18n::Get("system.settings_sections." . %%section_name%%)
					: %%section_name%%;
			?>
			<div class="dich-section">{{ %%section_disp_name%% }}</div>
			{{endif}}
					
			{{if (%%counter%% % 2) == 0}}<div class="column_2_0">{{else}}<div class="column_2_1">{{endif}}
				<label>{{%%value%%->Title}}</label>
				{{if %%value%%->DataType == SettingsEntryType::Text() }}
					<input type="text" name="id{{%%value%%->Id}}" class="dich-input-text" value="{{%%value%%->Value}}" />
				{{endif}}
				{{if %%value%%->DataType == SettingsEntryType::Number() }}
					<input type="text" class="dich-input-text" name="id{{%%value%%->Id}}" value="{{%%value%%->Value}}" />
				{{endif}}
				{{if %%value%%->DataType == SettingsEntryType::Boolean() }}
					<input type="checkbox" value="1" class="dich-input-checkbox" name="id{{%%value%%->Id}}" {{if %%value%%->Value == "1"}}checked{{endif}} />
				{{endif}}
				{{if %%value%%->DataType == SettingsEntryType::Password() }}
					<input type="text" class="dich-input-text" name="id{{%%value%%->Id}}" value="{{%%value%%->Value}}" />
				{{endif}}
				{{if %%value%%->DataType == SettingsEntryType::BigText() }}
					<textarea name="id{{%%value%%->Id}}" class="dich-input-textarea dich-input-textarea-small">{{%%value%%->Value}}</textarea>
				{{endif}}
			</div>
			
			<? %%counter%%++; ?>
			
			{{endfor}}
			
			<a href="#" onclick='doSaveSettings(forms["save"])' class="dich-action-button dich-input-submit">Сохранить</a>
		</form>
	</div>

	{{if Users::LoggedAs()->AccessId == 0}}
	<div id="dich-content-tab-add" class="dich-tabs-tab">
		
			<form name="submit" action="./add/" method="POST">
				<div class="column_2_0">
					<label>Наименование</label><input type="text" name="title" class="dich-input-text"/>
					<label>Идентификатор</label><input type="text" name="name" class="dich-input-text"/>
					<label>Секция</label><input type="text" name="section" class="dich-input-text"/>
					<label>Тип данных</label><select id="data_type" name="data_type" class="dich-input-select">
							{{foreach %%datatypevalue%% as %%datatypekey%% in Settings::Types() }}
								<option value="{{ %%datatypekey%% }}">{{ %%datatypevalue%% }}</option>
							{{endfor}}
					</select>
				</div>
				<div class="column_2_1">
					<label>Описание</label><textarea name="description" class="dich-input-textarea dich-input-textarea-small"></textarea>
					<label id="min_value_label">Минимальное значение</label><input id="min_value" type="text" name="min_value" class="dich-input-text"/>
					<label id="max_value_label">Максимальное значение</label><input id="max_value" type="text" name="max_value" class="dich-input-text"/>
					<label>Значение</label><textarea id="settings_entry_value" name="value" class="dich-input-textarea dich-input-textarea-small"></textarea>
				</div>
				
				<a href="#" onclick='doAddSettingsEntry(forms["submit"])' class="dich-action-button dich-input-submit">Добавить</a>
			</form>
		
	</div>

	<div id="dich-content-tab-list" class="dich-tabs-tab">

		<table class="dich-action-table">

			{{if count( @@values@@ ) == 0 }}		
			<tr class="dich-missing-data-row">
				<td>Пусто</td>
			</tr>
			{{endif}}

			{{foreach %%value%% in @@values@@}}		
			<?
				%%show_edit%% = true;
				%%show_delete%% = %%value%%->CanBeDeleted;
			?>
			<tr>
				<td>
					<a href='./{{%%value%%->Id }}/'>{{ %%value%%->Title }}</a>
					<span>{{ %%value%%->Name }}</span>
					{{if %%value%%->Phone != "" }}<span>{{%%value%%->Phone}}</span>{{endif}}
				</td>
				<td>
					{{if %%show_delete%%}}<a class="dich-action-button dich-action-delete" href="#" onclick='doDelSettingsEntry("{{ %%value%%->Id }}")'><span></span></a>{{endif}}
					{{if %%show_edit%%}}<a class="dich-action-button dich-action-edit" href='./{{ %%value%%->Id }}/'><span></span></a>{{endif}}
				</td>
			</tr>
			{{endfor}}
	
		</table>
		
	</div>
	{{endif}}

</div>

<script>
	$("#data_type").bind("change", function(){
	
		var lbShowMinAndMaxValues = (this.value == "{{ SettingsEntryType::Number() }}");
		
		$("#min_value_label")
		.add("#max_value_label")
		.add("#min_value")
		.add("#max_value")
			.toggle( lbShowMinAndMaxValues );
			
		if ( lbShowMinAndMaxValues )
			$("#settings_entry_value").attr( "value", "0" );
		
	});
	$("#data_type").trigger("change");
</script>

{{M l/bottom }}
