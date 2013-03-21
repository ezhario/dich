{{M l/top: "title"="Наполнение" }}
{{M l/menu }}

<script>
</script>

<div id="dich-content">
	<?
		%%tabs%% = array("dich-content-tab-all"=>"Разделы");
		%%anchnav%% = array("content"=>"dich-content-tab-all");
	
		if ( count(@@services@@) > 0)
		{
			%%tabs%%["dich-content-tab-services"] = "Сервисы";
			%%anchnav%%["services"] = "dich-content-tab-services";
		}
		
		if ( Users::LoggedAs()->AccessId == 0 )
		{
			%%tabs%%["dich-content-tab-filemanager"] = "Менеджер файлов";
			%%anchnav%%["file-manager"] = "dich-content-tab-filemanager";
		}
	?>

	{{if count(%%tabs%%) > 1}}
	{{M /l/e/tabs: "id"="dich-tabstop-2","items"=%%tabs%%, "anchor_navigation"=%%anchnav%% }}
	{{endif}}

	<div id="dich-content-tab-all" class="dich-tabs-tab{{if count(%%tabs%%) == 1}} dich-tabs-current{{endif}}">
		
		<table class="dich-action-table">

			{{if count( @@values@@ ) == 0 }}		
			<tr class="dich-missing-data-row">
				<td>Пусто</td>
			</tr>
			{{endif}}

			{{i  draw_table_item_with_hierarchy: "parent_id"=0, "items"=@@values@@, "hierarchy"=@@hierarchy@@, "path"="", "show_path"=true }}

			{{inline draw_table_item_with_hierarchy}}
				<? 
					%%item_id_tmp%% = @@parent_id@@;
					%%children%% = @@hierarchy[%%item_id_tmp%%]@@;
					%%margin%% = @@margin@@ + 1;
				?>
				{{foreach %%child_record%% in %%children%%}}
					<?
						%%value%% = @@items[%%child_record%%]@@;
						
						if ( !%%value%%->Disabled)
						{
						
							%%tmp_margin%% = %%margin%%;
							%%show_edit%% = (%%value%%->Class != "") && (%%value%%->Class != null);
							if (%%show_edit%%)
								%%show_edit%% = %%value%%->Logic()->IsEditingSupported();
							
							%%path%% = @@path@@ . %%value->Name%% . "/";
					?>

					<tr>
						<td>
							{{while %%tmp_margin%%-- > 1 }}<span class="dich-padding-cell"></span>{{endwhile}}
							<div>
								{{if %%show_edit%%}}<a href='./{{%%value->Id%%}}/'>{{ %%value->Title%% }}</a>{{else}}
								{{ %%value->Title%% }}
								{{endif}}
								{{if @@show_path@@ }}<span>{{ %%path%% }}</span>{{endif}}
							</div>
						</td>
						<td>
							{{if %%show_edit%%}}<a class="dich-action-button dich-action-edit" href='./{{%%value->Id%%}}/'><span></span></a>{{endif}}
						</td>
					</tr>

					{{i  draw_table_item_with_hierarchy: "parent_id"=%%child_record%%, "items"=@@items@@, "hierarchy"=@@hierarchy@@, "margin"=%%margin%%, "path"="%%path%%", "show_path"=true }}

					<?	
						}
					?>
				{{endfor}}
				
			{{endinline}}
			
		</table>
		
	</div>

	<div id="dich-content-tab-services" class="dich-tabs-tab">
		<table class="dich-action-table">

			{{if count( @@services@@ ) == 0 }}		
			<tr class="dich-missing-data-row">
				<td>Пусто</td>
			</tr>
			{{endif}}

			{{i  draw_table_item_with_hierarchy: "parent_id"=0, "items"=@@services@@, "hierarchy"=array("0"=array_keys(@@services@@)), "path"="", "show_path"=false }}
			
		</table>
	</div>

	{{if Users::LoggedAs()->AccessId == 0}}
	<div id="dich-content-tab-filemanager" class="dich-tabs-tab">
		<div id="dich-file-manager-placeholder">
			<div class="dich-file-manager"></div>
		</div>
		<script>
			$(document).ready( function() {
				// Код файлового менеджера
				var loInstance = $('#dich-file-manager-placeholder');

				// Инициализируем менеджер
				// SID передаётся для корректной междоменной работы аякса
				loInstance
					.find(".dich-file-manager")
					.dichFileManager({ 
						root: '/',
						sid: "{{ Users::SessionId() }}",
						onNavigate: function( poData )
						{
							var lsPath = "";

							if ( poData != null )
							{
								lsPath = poData.p;

								if (lsPath !== "/")
									lsPath = lsPath + "/";
							
								lsPath = " — " + lsPath;
							}

							loInstance.dialog( "option", "title", "{{ {{i18n manage.interface.filemanager.title}} }}" + lsPath );
						}
					});
			});
		</script>
	</div>
	{{endif}}
	
</div>

{{M l/bottom }}
