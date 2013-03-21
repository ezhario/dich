		<?/*
			Редактор DHTML 
			унаследован от http://imperavi.ru/redactor/ под лицензией MIT
		
		*/
		
			// И чуть-чуть проверок
			%%dhtml_contents%% = @@field@@->Value;

			if (%%dhtml_contents%% == "")
				%%dhtml_contents%% = '<p><br /></p>';
				
			%%lsIsImportant%% = (@@field@@->Important == TRUE) ? "true" : "false";
		?>
		
		<textarea name="{{@@field->Hash@@}}" id="{{@@field->Hash@@}}" style="height: 35em;" class="dich-input-textarea">{{ %%dhtml_contents%% }}</textarea>
		<script type="text/javascript">
			var {{@@field@@->Hash}}_instance = null;
		
				{{@@field@@->Hash}}_instance = $('#{{@@field->Hash@@}}').redactor({ 
					focus: true,
					toolbar: 'dich',
					css: ['/css/content.css'],
					autoclear: false
				});

			function {{@@field@@->Hash}}_onValidate()
			{
			    {{if (@@field@@->Important == TRUE) }}
			    var loElement = $("#{{@@field@@->Hash}}")[0];
			    var lbImportant = {{ %%lsIsImportant%% }};
			    
			    if ( loElement != null )
			    {
			        if (lbImportant)
			        {
			            //Заглушка
			        }
			    }
			    {{endif}}
			    
				return null;
			}
			function {{@@field@@->Hash}}_onBeforeSubmit()
			{
				if ({{@@field@@->Hash}}_instance != null)
					{{@@field@@->Hash}}_instance.syncCode();
			}
		</script>
