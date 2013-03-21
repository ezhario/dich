<?/* Стандартные элементы шаблона */?>
{{M l/top: "title"="Структура сайта" }}
{{M l/menu }}

<?/* Скрипты валидации и удаления */?>
<script>
	function doAddContent(poForm)
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

	function doDelContent(pnId, pnChildrenCount)
	{
		if (pnChildrenCount == 0)
		{
			var loInstance = 
				$("<div>Вы точно собираетесь удалить этот элемент?</div>")
				.dichDialog({ 
					title: "Вопрос",
					buttons: {
						yes: { title: "Да", className: "dich-dialog-button-ok", action: function() { 

								window.location.href = "./" + pnId + "/del/";
								return true; 
						}},
						no: { title: "Нет", className: "dich-dialog-button-cancel", action: function() { 
						
								return true; 
						}}
					}
				})
				.show();
		
			return;		
		}
		
		var loInstance = 
			$(
				'<div>Видите ли, у элемента сайта, который вы собираетесь удалить, есть подразделы. Сейчас укажите, что с этими подразделами нужно сделать:<form action="" id="dich-content-delete-action-form">' +
				'<br><input type="radio" name="action" value="move_to_parent" id="delete-action-to-parent" class="dich-input-radio" CHECKED>перенести к родителю</input>' +
				'<br><input type="radio" name="action" value="del_all" id="delete-action-delete" class="dich-input-radio">просто удалить их все</input>' +
				'</form></div>'
			)
			.dichDialog({ 
				title: "Как поступить?",
				buttons: {
					yes: { title: "Продолжить", className: "dich-dialog-button-ok", action: function() { 

							if ( $("#delete-action-to-parent").get(0).checked )
								window.location.href = "./" + pnId + "/del_shift/";
							else
								window.location.href = "./" + pnId + "/del/";

							return true; 
					}},
					no: { title: "Отказаться", className: "dich-dialog-button-cancel", action: function() { 
					
							return true; 
					}}
				}
			})
			.show();
	}
</script>

<div id="dich-content">

	<?/* Панель вкладок */?>
	{{M /l/e/tabs: "id"="dich-tabstop-1", "items"=array("dich-content-tab-all"="Разделы", "dich-content-tab-services"="Сервисы", "dich-content-tab-add"="Новый элемент"), "anchor_navigation"=array("services"="dich-content-tab-services", "new"="dich-content-tab-add") }}
	
	<?/* Вкладка добавления нового раздела */?>
	<div id="dich-content-tab-add" class="dich-tabs-tab">
		<form name="submit" action="./add/" method="POST">
			<div class="column_2_0">
				<label>Название</label><input type="text" name="title" class="dich-input-text"/>
				<label>Идентификатор</label><input type="text" name="name" class="dich-input-text"/>
				<label id="menu_entry_label">Виден в меню как</label><input id="menu_entry" type="text" name="menu_entry" class="dich-input-text"/>
			</div>
			<div class="column_2_1">
				<label id="is_service_label">Роль</label><select id="is_service" name="is_service" class="dich-input-select">
						<option value="0" selected>Раздел</option>
						<option value="1">Сервис</option>
				</select>
				<label id="p_id_label">Родительский элемент</label>
				<select id="p_id" name="p_id" class="dich-input-select">
						<option value="0" selected>Нет</option>
						{{foreach %%value%% as %%value_id%% in @@values@@ }}
							{{if !%%value%%->IsService }}
								<option value="{{ %%value_id%% }}">{{ %%value->Title%% }}</option>
							{{endif}}
						{{endfor}}
				</select>
				<label>Выключен</label><input type="checkbox" name="disabled" value="1" class="dich-input-checkbox" />
				<label>Показывается при построении меню</label><input type="checkbox" name="is_menu_entry" value="1" checked class="dich-input-checkbox" />
				<blockquote>Перепрофилировать раздел во что-нибудь полезнее заглушки вы сможете сразу как нажмёте кнопку «Добавить»</blockquote>
			</div>
		
		<a href="#" onclick='doAddContent(forms["submit"])' class="dich-action-button dich-input-submit">Добавить</a>
		</form>
		
		<?/* Скрипт обработки изменения пункта "Роль" */?>
		<script>
			$("#is_service").bind("change", function(){
	
				var lbIsService = (this.value == "1");
		
				$("#p_id_label")
				.add("#p_id")
				.add("#menu_entry_label")
				.add("#menu_entry")
					.toggle( !lbIsService );
			
				if ( lbIsService )
					$("#p_id").attr( "value", "0" );
			});
			$("#data_type").trigger("change");
		</script>
	</div>
		
	<?/* Вкладка иерархии контентных разделов */?>
	<div id="dich-content-tab-all" class="dich-tabs-tab">

		<table class="dich-action-table">

			{{if count( @@values@@ ) == 0 }}		
			<tr class="dich-missing-data-row">
				<td>Пусто</td>
			</tr>
			{{endif}}

			{{i  draw_table_item_with_hierarchy: "parent_id"=0, "items"=@@values@@, "hierarchy"=@@hierarchy@@ }}

			<?/* Инлайнер для отрисовки блока дерева */?>
			{{inline draw_table_item_with_hierarchy}}
				<? 
					%%item_id_tmp%% = @@parent_id@@;
					%%children%% = @@hierarchy[%%item_id_tmp%%]@@;
					%%margin%% = @@margin@@ + 1;
				?>
				{{foreach %%child_record%% in %%children%%}}
					<?
						%%value%% = @@items[%%child_record%%]@@;
						%%tmp_margin%% = %%margin%%;
						%%children_count%% = count( @@hierarchy[%%child_record%%]@@ );
						%%disabled%% = @@disabled@@ || %%value%%->Disabled;
					?>
						<tr{{if %%disabled%%}} class="dich-row-disabled"{{endif}}>
							<td>
								{{while %%tmp_margin%%-- > 1 }}<span class="dich-padding-cell"></span>{{endwhile}}
								
								{{if %%value->Precedence%% != (count(%%children%%)-1) }}<a class="dich-action-button dich-action-button-left dich-action-down" href='./{{%%value%%->Id}}/down/' title="Ниже"><span></span></a>{{else}}<span class="dich-action-button dich-action-button-left"></span>{{endif}}
								{{if %%value->Precedence%% != 0 }}<a class="dich-action-button dich-action-button-left dich-action-up" href='./{{%%value%%->Id}}/up/' title="Выше"><span></span></a>{{else}}<span class="dich-action-button dich-action-button-left"></span>{{endif}}
								<div><a href='./{{%%value->Id%%}}/'>{{ %%value->Title%% }}</a><span>{{ %%value->Name%% }}</span>{{if %%disabled%%}}<span class="dich-disabled-warning">Отключен</span>{{endif}}</div>
							</td>
							<td>
								<a class="dich-action-button dich-action-delete" href="#" onclick='doDelContent("{{%%value%%->Id}}", {{%%children_count%%}})' title="Удалить"><span></span></a>
								<a class="dich-action-button dich-action-edit" href='./{{%%value%%->Id}}/' title="Редактировать"><span></span></a>
								{{if %%value%%->Class != "" }}
								<a class="dich-action-button dich-action-preferences" href='./{{%%value%%->Id}}/logic_settings/' title="Настройки логики"><span></span></a>
								{{endif}}
							</td>
						</tr>

						{{i  draw_table_item_with_hierarchy: "parent_id"=%%child_record%%, "items"=@@items@@, "hierarchy"=@@hierarchy@@, "margin"=%%margin%%, "disabled"=%%disabled%% }}
					
				{{endfor}}
				
			{{endinline}}
			
		</table>
		
	</div>

	<?/* Вкладка сервисных разделов */?>
	<div id="dich-content-tab-services" class="dich-tabs-tab">

		<table class="dich-action-table">

			{{if count( @@services@@ ) == 0 }}		
			<tr class="dich-missing-data-row">
				<td>Пусто</td>
			</tr>
			{{endif}}

			{{i  draw_table_item_with_hierarchy: "parent_id"=0, "items"=@@services@@, "hierarchy"=array("0"=array_keys(@@services@@)) }}
			
		</table>
		
	</div>
</div>

{{M l/bottom }}
