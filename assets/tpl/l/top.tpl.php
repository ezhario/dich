<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>{{ @@title@@ }}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<?/* Скрипты */?>
	{{M l/e/js.safe }}
	{{if @@safe@@ !== true }}
	{{M l/e/js.unsafe }}
	{{endif}}
	
	<?/* Стили */?>
	{{if @@safe@@ !== true }}
	{{M l/e/css.unsafe }}
	{{endif}}
	{{M l/e/css.safe }}
	
</head>
<body>

