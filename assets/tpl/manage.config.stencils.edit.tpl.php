{{M l/top: "title"="Шаблоны | Редактирование" }}
{{M l/menu }}

<script>
	function doSaveStencil(poForm)
	{
		poForm.submit();
	}
</script>

<div id="dich-content">

	<?/* Шапка */?>
	<div id="dich-breadcrumbs"><ul>
		<li><a href="..">Шаблоны контента</a></li>
		<li><span>Редактирование</span></li>
	</ul></div>

	<br clear="both"/>	
	
	<?/* Основная форма редактирования */?>
	<form name="save" action="./save/" method="POST">
		<input type="hidden" name="id" value="{{ %%value->Id%% }}"/>
		
		<div class="column_2_0">
			<label>Название</label><input type="text" name="title" class="dich-input-text" value="{{ %%value%%->Title }}"/>
			<label>Идентификатор</label><input type="text" name="name" class="dich-input-text" value="{{ %%value%%->Name }}"/>
		</div>
		<div class="column_2_1">
			<label>Для верхнего уровня</label><input type="checkbox" name="is_top_level" class="dich-input-checkbox" value="1" {{if %%value%%->IsTopLevel }} checked {{endif}} {{if %%value%%->RefCount > 1 }} DISABLED {{endif}} />
			<label>Описание</label><textarea name="description" class="dich-input-textarea dich-input-textarea-small">{{ %%value%%->Description }}</textarea>
		</div>
		<br clear="both"/>
		<label>Код шаблона</label><textarea name="contents" id="contents" class="dich-input-textarea dich-input-textarea-big">{{ %%value%%->Contents }}</textarea>
		
		<a href="#" onclick='doSaveStencil(forms["save"])' class="dich-action-button dich-input-submit">Сохранить</a>
	</form>
		
	{{M l/e/codemirror: "id"="contents" }}

	{{M stuff/stencils.syntax.annotation: "caption"="Показать помощь по синтаксису шаблонизатора", "id"="dich-templates-help", "button_id"="dich-templates-help-button" }}
	
</div>

{{M l/bottom }}
