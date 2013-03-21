{{M l/top: "title"="Наполнение | Редактирование" }}
{{M l/menu }}

<div id="dich-content">

	<div id="dich-breadcrumbs"><ul>

	    <? %%lsRootLink%% = ""; ?>
	    {{foreach %%item%% in @@entities_queue@@}}<?%%lsRootLink%%.="/..";?>{{endfor}}

		<li><a href="..{{%%lsRootLink%%}}/">Все разделы</a></li>
		{{if (@@level@@ == 0) }}
		<li><span>«{{@@this@@->Content->Title}}»</span></li>
		{{else}}
        	<li><a href=".{{%%lsRootLink%%}}/">«{{@@this@@->Content->Title}}»</a></li>
        	<? %%lnItemCounter%% = 0; %%lsLink%% = ""; ?>
        	{{foreach %%item%% in @@entities_queue@@}}
        	    <?
        	        %%lnItemCounter%%++;
        	        %%lsLink%%.= %%item%%->Id."/";
        	    ?>
        	    {{if (%%lnItemCounter%% == count(@@entities_queue@@)) }}
        		<li><span>«{{%%item%%->Get("name")}}»</span></li>
        	    {{else}}
        	    <li><a href=".{{%%lsRootLink%%}}/{{%%lsLink%%}}">«{{%%item%%->Get("name")}}»</a></li>
        	    {{endif}}
        	{{endfor}}
		{{endif}}
	</ul></div>
	<br />
	
    <script>
	function doSaveContent(poForm)
	{
		var lsValidationMessage = null;
		
		{{foreach %%field%% as %%field_name%% in @@content_adapter->Fields@@ }}
		
		if (lsValidationMessage == null)
			if (window.{{%%field%%->Hash}}_onValidate)
				lsValidationMessage = {{%%field%%->Hash}}_onValidate();

		{{endfor}}

		if (lsValidationMessage != null)
		{
			alert(lsValidationMessage);
			return false;
		}
				
		{{foreach %%field%% as %%field_name%% in @@content_adapter->Fields@@ }}
		
		if (window.{{%%field%%->Hash}}_onBeforeSubmit)
			{{%%field%%->Hash}}_onBeforeSubmit();

		{{endfor}}

		poForm.submit();
	}
	function doAddContent(poForm)
	{
		var lsValidationMessage = null;
		
		{{foreach %%field%% as %%field_name%% in @@current_level_adapter->Fields@@ }}
		
		if (lsValidationMessage == null)
			if (window.{{%%field%%->Hash}}_onValidate)
				lsValidationMessage = {{%%field%%->Hash}}_onValidate();

		{{endfor}}

		if (lsValidationMessage != null)
		{
			alert(lsValidationMessage);
			return false;
		}
				
		{{foreach %%field%% as %%field_name%% in @@current_level_adapter->Fields@@ }}
		
		if (window.{{%%field%%->Hash}}_onBeforeSubmit)
			{{%%field%%->Hash}}_onBeforeSubmit();

		{{endfor}}

		poForm.submit();
	}
	function doSaveMetadata(poForm)
	{
		poForm.submit();
	}
	function doDelItem(pnId)
	{
		if (confirm("Продолжить удаление?"))
			window.location.href = "./" + pnId + "/del/";
	}
	</script>
	
	<?
	    %%current_level_name%% = @@current_level_dataset@@->Title;
	    %%save_id%% = (@@level@@ == 0 ) ? @@content@@->Id : @@content_adapter@@->Entity->Id;
	?>
	
	{{if (@@level@@==0) }}
	{{M /l/e/tabs: "id"="dich-tabstop-4","items"=array("dich-content-tab-subitems"=%%current_level_name%%, "dich-content-tab-subitems-add"="Добавить", "dich-content-tab-edit"="Корневая страница", "dich-content-tab-meta"="Метаданные") }}
    {{else}}
        {{if (@@level@@ == count(@@structure@@)) }}
    	    {{M /l/e/tabs: "id"="dich-tabstop-4","items"=array("dich-content-tab-edit"="Наполнение") }}
        {{else}}
    	    {{M /l/e/tabs: "id"="dich-tabstop-4","items"=array("dich-content-tab-subitems"=%%current_level_name%%, "dich-content-tab-subitems-add"="Добавить", "dich-content-tab-edit"="Наполнение") }}
    	{{endif}}
    {{endif}}
    
    {{if (@@level@@ <= count(@@structure@@)) }}
	<div class="dich-tabs-tab" id="dich-content-tab-subitems">
	
		<table class="dich-action-table">

			{{if count( @@items@@ ) == 0 }}		
			<tr class="dich-missing-data-row">
				<td>Пусто</td>
			</tr>
			{{endif}}

			{{foreach %%item%% in @@items@@}}	
			<? %%entity%% = %%item%%->Entity; ?>	
			<tr>
				<td>
					<a href='./{{%%entity%%->Id}}/' title="Редактировать">{{ %%item%%->Field("name")->Value }}</a>
					<span>{{ %%item%%->Field("tag")->Value }}</span>
				</td>
				<td>
					<a class="dich-action-button dich-action-delete" href="#" onclick='doDelItem("{{%%entity%%->Id}}")' title="Удалить"><span></span></a>
					<a class="dich-action-button dich-action-edit" href='./{{%%entity%%->Id}}/' title="Редактировать"><span></span></a>
					{{if %%item%%->Precedence != (count(@@items@@)-1) }}<a class="dich-action-button dich-action-down" href='./{{%%item%%->Id}}/down/' title="Ниже"><span></span></a>{{else}}<span class="dich-action-button dich-action-button-right"></span>{{endif}}
					{{if %%item%%->Precedence != 0 }}<a class="dich-action-button dich-action-up" href='./{{%%item%%->Id}}/up/' title="Выше"><span></span></a>{{else}}<span class="dich-action-button dich-action-button-right"></span>{{endif}}
				</td>
			</tr>
			{{endfor}}
		
		</table>
		
		<br/><br/>

	</div>

	<div class="dich-tabs-tab" id="dich-content-tab-subitems-add">

		<form name="add" action="./add/" method="POST">
			<input type="hidden" name="id" value="{{ @@current_level_dataset_resource@@->Id }}"/>

			{{foreach %%field%% as %%field_name%% in @@current_level_adapter->Fields@@ }}
				{{if count(@@current_level_adapter@@->Fields) > 1}}<label>{{%%field->Title%%}}</label>{{endif}}
				<?=Templates::Code(%%field%%->DrawEditFormPart());?>
			{{endfor}}

			<a href="#" onclick='doAddContent(forms["add"])' class="dich-action-button dich-input-submit">Продолжить</a>
		</form>

	</div>
	{{endif}}

	{{if (@@level@@==0) }}
	<div class="dich-tabs-tab" id="dich-content-tab-meta">
		<form name="save-metadata" action="./metadata/save/" method="POST">

		<input type="hidden" name="id" value="{{ %%value->Id%% }}" />

		<textarea name="meta" class="dich-input-textarea dich-input-textarea-small">{{ @@value@@->Meta }}</textarea>
	
		<a href="#" onclick='doSaveMetadata(forms["save-metadata"])' class="dich-action-button dich-input-submit">Сохранить</a>

		</form>
	</div>		
	{{endif}}

	<div class="dich-tabs-tab" id="dich-content-tab-edit">
	
		<form name="save" action="./save/" method="POST">
			<input type="hidden" name="id" value="{{ %%save_id%% }}"/>

			{{foreach %%field%% as %%field_name%% in @@content_adapter->Fields@@ }}
				{{if count(@@content_adapter@@->Fields) > 1}}<label>{{%%field->Title%%}}</label>{{endif}}
				<?=Templates::Code(%%field%%->DrawEditFormPart());?>
			{{endfor}}

			<a href="#" onclick='doSaveContent(forms["save"])' class="dich-action-button dich-input-submit">Сохранить</a>
		</form>
		
	</div>

</div>

{{M l/bottom }}
