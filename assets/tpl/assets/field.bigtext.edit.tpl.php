		<textarea name="{{@@field->Hash@@}}" id="{{@@field->Hash@@}}" class="dich-input-textarea dich-input-textarea-small">{{ @@field->Value@@ }}</textarea>
		<?
			%%lsIsImportant%% = (@@field@@->Important == TRUE) ? "true" : "false";
		?>
		
		<script type="text/javascript">
			function {{@@field@@->Hash}}_onValidate()
			{
			    {{if (@@field@@->Important == TRUE) }}

			    var loElement = $("#{{@@field@@->Hash}}")[0];
			    var lbImportant = {{ %%lsIsImportant%% }};
			    
			    if ( loElement != null )
			        if (lbImportant)
			        {
			            if ( dichTextIsEmpty( loElement.value ) )
			            {
			                return "Обязательно укажите значение поля «{{@@field@@->Title}}»!";
			            }
			        }
			    
			    {{endif}}
			    
				return null;
			}
			function {{@@field@@->Hash}}_onBeforeSubmit()
			{
			}
		</script>
