<?/*

	Шаблон вставки редактора кода CodeMirror
	
	Параметры:
		@@id@@ - идентификатор элемента привязки редактора

*/?>
<script language="JavaScript">
	var codemirror_{{@@id@@}} = CodeMirror.fromTextArea("{{@@id@@}}", {
		lineNumbers: true,
		opening: ["<?="<?";?>"],
		parserfile: [
			"parsexml.js", 			"parsecss.js", 		"tokenizejavascript.js", 
			"parsejavascript.js",	"tokenizephp.js",	"parsephp.js",
			"parsephphtmlmixed.js"],
		iframeClass: "CodeMirror-iframe",
		stylesheet: [
			"/assets/js/CodeMirror-0.8/css/xmlcolors.css", 
			"/assets/js/CodeMirror-0.8/css/jscolors.css", 
			"/assets/js/CodeMirror-0.8/css/csscolors.css", 
			"/assets/js/CodeMirror-0.8/css/phpcolors.css"],
		path: "/assets/js/CodeMirror-0.8/",
		continuousScanning: 500
	});
</script>
