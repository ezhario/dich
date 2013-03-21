{{M l/top: "title"="Шаблоны" }}
{{M l/menu }}

<script>
	function doAddStencil(poForm)
	{
		poForm.submit();
	}

	function doDelStencil(pnId)
	{
		if (confirm("Продолжить удаление?"))
			window.location.href = "./" + pnId + "/del/";
	}
</script>

<?
    %%laTopLevel%% = array();
    %%laBlocks%% = array();
    
    foreach ( @@values@@ as %%loStencil%% )
        if ( %%loStencil%%->IsTopLevel )
            %%laTopLevel%%[] = %%loStencil%%;
        else
            %%laBlocks%%[] = %%loStencil%%;
?>

<div id="dich-content">

	{{M /l/e/tabs: "id"="dich-tabstop-2","items"=array("dich-content-tab-toplevel"="Шаблоны верхнего уровня", "dich-content-tab-blocks"="Блоки", "dich-content-tab-add"="Новый шаблон") }}
	
	<div id="dich-content-tab-add" class="dich-tabs-tab">
	
			<form name="submit" action="./add/" method="POST">
				<div class="column_2_0">
					<label>Название</label><input type="text" name="title" class="dich-input-text"/>
					<label>Идентификатор</label><input type="text" name="name" class="dich-input-text"/>
				</div>
				<div class="column_2_1">
					<label>Для верхнего уровня</label><input type="checkbox" name="is_top_level" class="dich-input-checkbox" value="1" />
					<label>Описание</label><textarea name="description" class="dich-input-textarea dich-input-textarea-small"></textarea>
				</div>
				<br clear="both"/>
				<label>Код шаблона</label><textarea name="contents" id="contents" class="dich-input-textarea dich-input-textarea-big"></textarea>
				
				<a href="#" onclick='doAddStencil(forms["submit"])' class="dich-action-button dich-input-submit">Добавить</a>
			</form>
			
			{{M l/e/codemirror: "id"="contents" }}
	
			{{M stuff/stencils.syntax.annotation: "caption"="Показать помощь по синтаксису шаблонизатора", "id"="dich-templates-help", "button_id"="dich-templates-help-button" }}
	</div>

	<div id="dich-content-tab-toplevel" class="dich-tabs-tab">
		{{i  draw_table_items: "table_values"=%%laTopLevel%% }}
	</div>

	<div id="dich-content-tab-blocks" class="dich-tabs-tab">
		{{i  draw_table_items: "table_values"=%%laBlocks%% }}
	</div>

		{{inline draw_table_items}}
		
		<table class="dich-action-table">

			{{if count( @@table_values@@ ) == 0 }}		
			<tr class="dich-missing-data-row">
				<td>Пусто</td>
			</tr>
			{{endif}}

			{{foreach %%value%% in @@table_values@@}}		
			<tr>
				<td>
					<a href='./{{%%value%%->Id}}/' title="Редактировать">{{ %%value%%->Title }}</a>
					<span>{{%%value%%->Name }}</span>
					{{if %%value%%->RefCount != 0 }}<span>Жёстких ссылок: {{%%value%%->RefCount }}</span>{{endif}}
					{{if %%value%%->IsTopLevel }}<span>Шаблон верхнего уровня</span>{{endif}}
				</td>
				<td>
					{{if %%value%%->RefCount == 0 }}<a class="dich-action-button dich-action-delete" href="#" onclick='doDelStencil("{{%%value%%->Id}}")' title="Удалить"><span></span></a>{{else}}<span class="dich-action-button dich-action-button-right"></span>{{endif}}
					<a class="dich-action-button dich-action-edit" href='./{{%%value%%->Id}}/' title="Редактировать"><span></span></a>
				</td>
			</tr>
			{{endfor}}
		
		</table>
		
		<br/><br/>

		{{endinline}}

</div>

{{M l/bottom }}
