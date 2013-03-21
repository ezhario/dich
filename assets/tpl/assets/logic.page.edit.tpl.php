{{M l/top: "title"="Наполнение | Редактирование" }}
{{M l/menu }}

<div id="dich-content">

	<div id="dich-breadcrumbs"><ul>
		<li><a href="..">Все разделы</a></li>
		<li><span>«{{@@value->Title@@}}»</span></li>
	</ul></div>
	<br />

	<script>
	function doSaveContent(poForm)
	{
		var lsValidationMessage = null;
		
		{{foreach %%field%% as %%field_name%% in @@content->Fields@@ }}
		
		if (lsValidationMessage == null)
			if (window.{{%%field%%->Hash}}_onValidate)
				lsValidationMessage = {{%%field%%->Hash}}_onValidate();

		{{endfor}}

		if (lsValidationMessage != null)
		{
			alert(lsValidationMessage);
			return false;
		}
				
		{{foreach %%field%% as %%field_name%% in @@content->Fields@@ }}
		
		if (window.{{%%field%%->Hash}}_onBeforeSubmit)
			{{%%field%%->Hash}}_onBeforeSubmit();

		{{endfor}}

		poForm.submit();
	}
	function doSaveMetadata(poForm)
	{
		poForm.submit();
	}
	</script>

	{{M /l/e/tabs: "id"="dich-tabstop-4","items"=array("dich-content-tab-all"="Правка", "dich-content-tab-meta"="Метаданные") }}

	<div class="dich-tabs-tab" id="dich-content-tab-all">
		<form name="save" action="./save/" method="POST">
			<input type="hidden" name="id" value="{{ %%value->Id%% }}"/>

			{{foreach %%field%% as %%field_name%% in @@content->Fields@@ }}
				{{if count(@@content@@->Fields) > 1}}<label>{{%%field->Title%%}}</label>{{endif}}
				<?=Templates::Code(%%field%%->DrawEditFormPart());?>
			{{endfor}}

			<a href="#" onclick='doSaveContent(forms["save"])' class="dich-action-button dich-input-submit">Сохранить</a>
		</form>
	</div>

	<div class="dich-tabs-tab" id="dich-content-tab-meta">
		<form name="save-metadata" action="./metadata/save/" method="POST">

		<input type="hidden" name="id" value="{{ %%value->Id%% }}" />

		<textarea name="meta" class="dich-input-textarea dich-input-textarea-small">{{ @@value@@->Meta }}</textarea>
	
		<a href="#" onclick='doSaveMetadata(forms["save-metadata"])' class="dich-action-button dich-input-submit">Сохранить</a>

		</form>
	</div>		
</div>

{{M l/bottom }}
