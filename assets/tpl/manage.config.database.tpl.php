{{M l/top: "title"="База данных" }}
{{M l/menu }}

<div id="dich-content">


	<table class="dich-action-table">

		{{if count( %%tables%% ) == 0 }}		
		<tr class="dich-missing-data-row">
			<td>В базе нет таблиц</td>
		</tr>
		{{endif}}

		{{foreach %%table%% in %%tables%%}}		
		<tr>
			<td>{{ %%table%% }} </td>
		</tr>
		{{endfor}}
	
	</table>	

	<br />
	
	<a class="dich-input-button" href="./deploy/">Разместить таблицы БД</a>
	
</div>

{{M l/bottom }}
