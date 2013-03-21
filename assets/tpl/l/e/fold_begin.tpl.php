<?/*
	Начало сворачивающегося блока.
	
	Параметры:
		
		title - заголовок сворачивающегося блока 
		calm - признак того, что блок должен иметь спокойный фон. Для установки спокойного фона должен равняться "true"
		expanded - признак того, что блок распахнут
		nothumb - признак отсутствия тамба
		thin - 	признак узкого блока
*/?>

<div class="dich-fold {{if @@calm@@ == "true" }} dich-fold-calm {{endif}}{{if @@expanded@@ == "true" }} dich-fold-expanded {{endif}}{{if @@thin@@ == "true" }} dich-fold-thin {{endif}}">
	<div class="dich-fold-header">
		<div class="dich-fold-thumb {{if @@nothumb@@ == "true" }} dich-not-shown {{endif}}"></div>
		{{if @@title@@ != ""}}<h3>{{ @@title@@ }}</h3>{{endif}}
	</div>
	<div class="dich-fold-body">
