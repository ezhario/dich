<?/*

	Шаблон вставки группы вкладок для переключения видимости блоков.
	
	Параметры:
		@@id@@ - идентификатор создаваемого корневого элемента вкладок
		@@items@@ - массив вида "идентификатор блока" => "текст, показываемый во вкладке"
		@@anchor_navigation@@ - массив вида "значение якоря" => "идентификатор вкладки"
		
	В переменной @@items@@ находится список табов, которые необходимо создать.
	В переменной @@anchor_navigation@@ находится (опциональный) список значений якоря,
	при которых переходит переключение на заданную вкладку.
	
	Пример использования: 
	{{M /l/e/tabs: "id"="tabstop-1", "items"=array("tab-all"="Разделы", "tab-services"="Сервисы"), "anchor_navigation"=array("services"="services") }}
*/?>
<? %%counter%% = 0; ?>
	<div class="dich-tabs" id="{{ @@id@@ }}">
		<div class="dich-tabs-header">
			<ul>
				{{foreach %%item_contents%% as %%item_id%% in @@items@@}}
				<li id="{{@@id@@}}_{{{%%item_id%%}}}"{{if %%counter%%==0}} class="dich-tabs-current"{{endif}}>{{%%item_contents%%}}</li>
				<? %%counter%%++; ?>
				{{endfor}}
			</ul>
		</div>
	</div>
	
	<script>
		$(document).ready( function() {
		
		<? %%counter%% = 0; ?>
		{{foreach %%item_contents%% as %%item_id%% in @@items@@}}
		
			{{if %%counter%% == 0 }}
			$("#{{%%item_id%%}}").addClass("dich-tabs-current");
			{{endif}}

			$("#{{@@id@@}}_{{{%%item_id%%}}}").bind( 'click', function() {
			
			    // Проверим, не кликнули ли мы уже по активному табу
				if ( $(this).hasClass("dich-tabs-current") )
					return;
			
				// Активация табов
				$(this).parent().children().removeClass("dich-tabs-current");
				$(this).addClass("dich-tabs-current");
				
				// Активация блоков
				{{foreach %%item2_contents%% as %%item2_id%% in @@items@@}}{{if %%item2_id%% != %%item_id%%}}
				$("#{{%%item2_id%%}}").removeClass("dich-tabs-current");
				{{endif}}{{endfor}}
				
				$("#{{%%item_id%%}}").addClass("dich-tabs-current");
			});
		
		<? %%counter%%++; ?>
		{{endfor}}

		var lsAnchorValue = $.url.attr("anchor");

		{{foreach  %%tab_id%% as %%anchor_value%% in @@anchor_navigation@@}}
		
		if (lsAnchorValue == "{{{%%anchor_value%%}}}") $("#{{@@id@@}}_{{{%%tab_id%%}}}").trigger('click');
		{{endfor}}
		
		});
	</script>

