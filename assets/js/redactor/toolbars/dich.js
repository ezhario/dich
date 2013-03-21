var RTOOLBAR = {
	undo:   {exec: 'Undo', name: 'undo', title: RLANG.undo },
	redo: 	{exec: 'Redo', name: 'redo', title: RLANG.redo},	
	separator10: { name: 'separator' },		
	fullscreen: { name: 'fullscreen', title: RLANG.fullscreen, func: 'fullscreen' },	
	separator1: { name: 'separator' },			
	styles: 
	{
		name: 'styles', title: RLANG.styles, func: 'show', 
		dropdown: 
		{
			p: 			{exec: 'formatblock', name: 'p', title: RLANG.paragraph},
			blockquote: {exec: 'formatblock', name: 'blockquote', title: RLANG.quote},
			code: 		{exec: 'formatblock', name: 'pre', title: RLANG.code},
			h1: 		{exec: 'formatblock', name: 'h1', title: RLANG.header1, style: 'font-size: 26px;'},
			h2: 		{exec: 'formatblock', name: 'h2', title: RLANG.header2, style: 'font-size: 20px;'},
			h3: 		{exec: 'formatblock', name: 'h3', title: RLANG.header3, style: 'font-size: 16px; font-weight: bold;'},																	
			h4: 		{exec: 'formatblock', name: 'h4', title: RLANG.header4, style: 'font-size: 12px; font-weight: bold;'}																	
		}
	},
	separator9: { name: 'separator' },				
	bold: 	{exec: 'Bold', name: 'bold', title: RLANG.bold},				
	italic: 	{exec: 'italic', name: 'italic', title: RLANG.italic},				
	strikethrough: 	{exec: 'Strikethrough', name: 'strikethrough', title: RLANG.strikethrough},				
	superscript: 	{exec: 'superscript', name: 'superscript', title: RLANG.superscript},	
	separator2: { name: 'separator' },								
	hilite: 	  {name: 'backcolor', title: RLANG.backcolor, func: 'showHilite'},
	fgcolor: 	  {name: 'fontcolor', title: RLANG.fontcolor, func: 'showFgcolor'},
	separator3: { name: 'separator' },			
	ul: 	 {exec: 'insertunorderedlist', name: 'unorderlist', title: '&bull; ' + RLANG.unorderedlist},
	ol: 	 {exec: 'insertorderedlist', name: 'orderlist', title: '1. ' + RLANG.orderedlist},
	outdent: {exec: 'outdent', name: 'outdent', title: '< ' + RLANG.outdent},
	indent:  {exec: 'indent', name: 'indent', title: '> ' + RLANG.indent},
	separator4: { name: 'separator' },			
	JustifyLeft: 	 {exec: 'JustifyLeft', name: 'align_left', title: RLANG.align_left},					
	JustifyCenter: 	 {exec: 'JustifyCenter', name: 'align_center', title: RLANG.align_center},
	JustifyRight: {exec: 'JustifyRight', name: 'align_right', title: RLANG.align_right},
	separator5: { name: 'separator' },			
	image: { name: 'image', title: RLANG.image, func: 'showImage' },
	table: { name: 'table', title: RLANG.table, func: 'showTable' },
	video: { name: 'video', title: RLANG.video, func: 'showVideo' },
	link: 
	{
		name: 'link', title: RLANG.link, func: 'show',
		dropdown: 
		{
			link: 	{name: 'link', title: RLANG.link_insert, func: 'showLink'},
			unlink: {exec: 'unlink', name: 'unlink', title: RLANG.unlink}
		}			
	},
	separator11: { name: 'separator' },	
	html: { name: 'html', title: RLANG.html, func: 'toggle' }
};
