if(jQuery) ( function($){
	
	$.extend( {
		// Server-side RPC
		dichFileManagerRPC: function ( psMethod, poParameters, peHandler, poContext, poOptions )
		{
			this.o = $.extend( {
				script: '/filemanager.php',
				onNavigate: function( poData ){}
			}, poOptions );

			$.ajax({
				context: poContext,
				data: $.extend(poParameters, { a: psMethod }),
				dataType: "json",
				success: peHandler,
				type: "POST",
				url: this.o.script
			});

			//return $.post( this.o.script, $.extend(poParameters, { a: psMethod }), peHandler, "json" );			
		}
	} );
	
	// Main entry point
	$.fn.extend( {
		dichFileManager: function( poOptions )
		{				
			return new dichFileManager( this, poOptions );
		}
	} );

	// ***************
	// dichFileManager
	// ***************
	
	function dichFileManager( poElement, poOptions ) 
	{
		this.o = $.extend( {	
			root: '/',
			treeViewExpandSpeed : 100,
			treeViewCollapseSpeed: 100,
			treeViewExpandEasing: null,
			treeViewCollapseEasing: null,
			script: '/filemanager.php',
			sid: ''
		}, poOptions );
		
		this.$self = $(poElement);	
		this.init();
	}
	
	dichFileManager.prototype = 
	{	
		init: function()
		{	
			this.$self.html('<div class="dich-file-manager-tree-container"><div class="dich-file-manager-tree"></div></div><div class="dich-file-manager-data-container"><div class="dich-file-manager-data-pane"><div class="dich-file-manager-action-list"></div><div class="dich-file-manager-data-list"></div></div></div>');

			this.oFileList = new dichFileManagerFileList( this, this.$self.find(".dich-file-manager-data-list").get(0), this.o, {
				doQueryItems:			this.doQueryItems,
				onItemSelectionChanged:	this.onFileListSelectionChanged,
				onDoubleClicked:		this.onFileListItemDoubleClicked
			});

			this.oTreeView = new dichFileManagerTreeView( this, this.$self.find(".dich-file-manager-tree").get(0), this.o, {
				onItemClicked:			this.onTreeViewItemClicked, 
				doQueryItems:			this.doQueryItems,
				onNodeSelectionChanged:	this.onTreeViewSelectionChanged
			});
		},
		
		// events
		doQueryItems: function( psPath, peCallback, poContext, poFileManager )
		{
			$.dichFileManagerRPC( "ls", {p: psPath}, peCallback, poContext, { script: poFileManager.o.script, sid: poFileManager.o.sid } );
		},
		onTreeViewItemClicked: function( poNode )
		{
		},
		onFileListItemDoubleClicked: function( poItem )
		{
			if (poItem === null)
				return false;
			
			var oFileManager = poItem.oFileList.oFileManager;
			var oNodes = oFileManager.oTreeView.oSelectedNode.oNodes;
			
			for ( var i=0; i<oNodes.length; ++i )
				if ( oNodes[i].oData.p == poItem.oData.p )
				{
					oNodes[i].select();
					return true;
				}
		},
		onTreeViewSelectionChanged: function( poOldNode, poNewNode, poTreeView )
		{
			poTreeView.oFileManager.oFileList.navigate( poNewNode == null ? null : poNewNode.oData );
		},
		
		//actions
		makeDirectory: function( psPath, peCallback, poContext, poFileManager )
		{
			$.dichFileManagerRPC( "mkdir", {p: psPath}, peCallback, poContext, { script: poFileManager.o.script, sid: poFileManager.o.sid } );
		}
	}

	// ********************************
	// dichFileManagerFileListSelection
	// ********************************

	function dichFileManagerFileListSelection( poFileList )
	{
		this.oFileList = poFileList;
		this.oItems = Array();
		this.init();
	}
	
	dichFileManagerFileListSelection.prototype =
	{
		init: function()
		{
		},
		clear: function ()
		{
			var loItem = null;
			
			for ( var i=0; i<this.oItems.length; ++i )
			{
				loItem = this.oItems[i];
				if ( loItem != null )
					loItem.deselect( true );
			}
			
			this.oItems = new Array();
			this.oFileList.onSelectionChanged();
		},
		add: function ( poItem )
		{
			if (poItem != null)
				if (poItem.oFileList != this.oFileList)
					return;
					
			this.oItems.push( poItem );
			poItem.select( true );
			this.oFileList.onSelectionChanged();
		},
		remove: function ( poItem )
		{
			if (poItem != null)
				if (poItem.oFileList != this.oFileList)
					return;

			this.oItems = $.map( this.oItems, function( a ){ if (a == poItem) return null; return a; } );
			poItem.deselect( true );
			this.oFileList.onSelectionChanged();
		}
	}

	// ***********************
	// dichFileManagerFileList
	// ***********************

	function dichFileManagerFileList( poFileManager, poElement, poOptions, poEvents) 
	{
		this.o = poOptions;
		this.oFileManager = poFileManager;
		this.events = $.extend(	{
			doQueryItems: null,				// Path, Callback, Node context
			onItemSelection: null,			// NewItem, FileList
			onItemSelectionChanged: null,	// OldItem, NewItem, FileList
			onEnterItem: this.onEnterItem,
			onLeaveItem: this.onLeaveItem,
			onDoubleClicked: null
		}, poEvents );
		this.oLocation = null;
		this.oItems = Array();
		this.oActionPane = null;
		this.oSelection = null;		
		this.$tmpItem = null;
		
		this.$self = $(poElement);
		this.init();
	}

	dichFileManagerFileList.prototype = 
	{
		init: function()
		{	
			this.oSelection = new dichFileManagerFileListSelection( this );
			this.oActionPane = new dichFileManagerFileListActionPane( this );
			this.$container = $('<ul></ul>');
			this.$self.append( this.$container );
		},
		
		// methods
		navigate: function( poData )
		{
			if (poData == null)
			{
				this.removeAll();
				this.oLocation = null;
			}
			else
			{
				this.oLocation = poData;
				this.refreshContents();
			}
		},
		refreshContents: function( )
		{
			this.removeAll();
			this.setWaiting( true );
			
			var lsPath = this.oLocation.p;

			if (lsPath != '/')
				lsPath += '/';
				
			this.doQueryItemsDefault( 
				lsPath, 
				function (poData) 
				{
					this.setWaiting( false );
				
					if (poData != null)
					{
						var loData = null;
						var lsClassName = "";
					
						for ( var i = 0; i < poData.length; ++i)
						{
							loData = poData[i];
							this.oItems.push( new dichFileManagerFileListItem( this, loData, this.events ) );
						}
					}
				}, 
				this,
				this.oFileManager
			);
		},
		removeAll: function()
		{
			this.oSelection.clear();
			this.oItems = Array();
			this.$container.children().remove();			
		},
		setWaiting: function( pbMode )
		{
		},
		
		// default events
		doQueryItemsDefault: function( psPath, peCallback, poContext, poFileManager ) 
		{ 
			if (this.events.doQueryItems != null) 
				this.events.doQueryItems( psPath, peCallback, poContext, poFileManager );
		},
		onSelectionChanged: function()
		{
			this.oActionPane.refreshContents( this.oSelection.oItems );
		},
		onEnterItem: function( poItem ) 
		{ 
			if (poItem.oFileList.$tmpItem == null) 
			{
				var loItem = $('<div class="dich-file-manager-node-action-list" />');

				if (poItem.oData.i == true)
				{
					loItem.append(
						$('<div class="dich-file-manager-action-image" />')
							.button( { icons: { primary: "ui-icon-image" }, text: false } )
							.bind('click', poItem, function (event) 
							{
								var loItem = event.data; 
								
								/*$( '<div><img src="' + loItem.oData.p + '" style="width:100%;"></div>' )
									.dialog({
										modal: true,
										draggable: false,
										maxWidth: "800px",
										maxHeight: "600px",
										minWidth: "400px",
										minHeight: "400px",
										width: "400px",
										height: "400px",
										resizable: false,
										title: loItem.oData.p
									});*/

								var loTItem = $('<div class="reveal-modal xlarge"><div style="overflow:auto"><img src="' + loItem.oData.p + '" /></div><a class="close-reveal-modal">&#215;</a></div>');
								
								loTItem.reveal();
						
								return false;
							})
					);
				}
				
				poItem.$self.find("A").append( loItem );
				poItem.oFileList.$tmpItem = loItem;
			}
		},
		onLeaveItem: function( poItem ) 
		{
			if (poItem.oFileList.$tmpItem != null) 
			{
				poItem.oFileList.$tmpItem.remove();
				poItem.oFileList.$tmpItem = null;
			}
		}
	}

	// *********************************
	// dichFileManagerFileListActionPane
	// *********************************

	function dichFileManagerFileListActionPane( poFileList )
	{
		this.oFileList = poFileList;
		this.$self = null;
		this.init();
	}
	
	dichFileManagerFileListActionPane.prototype =
	{
		init: function()
		{
			// Ссылка на свою обртку
			this.$self = this.oFileList.oFileManager.$self.find( '.dich-file-manager-action-list' );
			
			// Действия
			this.oActions = 
			{
				// Аплоадинг файлов
				// ----------------
				uploadFiles: 
				{ 
					oActionPane : this,
					button: $('<a href="#" alt="Загрузить файлы"></a>').button( {icons: { primary: "ui-icon-arrowthickstop-1-s" }, text: false } ),
					init: function ( poThis )
					{
						poThis.button.bind( 'click', poThis, function(event)
						{
							var loThis = event.data;
							var loActionPane = loThis.oActionPane;
							var loForm = $( '<form method="post" action="' + loActionPane.oFileList.oFileManager.o.script + '" enctype="multipart/form-data"></form>' );
							var loAddFileButton = $( '<a />', {
							    class : 'dich-action-button dich-input-submit dich-action-button-left',
							    text : 'Ещё один файл...'
							});
							
							var lsIFrameName = 'fmUploader' + (new Date().getTime());
							var loIFrame = $('<iframe />', { id : lsIFrameName, name : lsIFrameName });
							
							var lnItemNumber = 0;
							
							loAddFileButton.click( function () {
							
							    loForm.append( $( '<input name="file' + lnItemNumber  +'" size="20" type="file" />') );
							    lnItemNumber ++;
							
							});
							
							loAddFileButton.click();
							
							
							loForm.append(
								$('<input type="hidden" name="sid" value="' + loActionPane.oFileList.oFileManager.o.sid + '" />')
							);
							
							loIFrame.load( function(event){
							
							    /*loIFrame
							    .contents()
							    .find('html')
							    .html('<body>123</body>');*/
							
							    loIFrame
							    .contents()
							    .find('body')
							    .append( loForm );
								
							});
							
							// Соберём диаложек
							$('<div />', { class : 'dich-annotation reveal-modal large' })
							.append( $( '<div class="reveal-title">Загрузка файлов</div>' ) )
							.append( $( '<a class="close-reveal-modal">&#215;</a>' ) )
							.append( loIFrame )
							.append( loAddFileButton )
							// И покажем его
							/*.dialog({
								modal: true,
								draggable: true,
								resizable: false,
								width: "400px",
								title: "Загрузка файлов",
								close: function()
								{
									$(this).remove();
								},
								buttons:
								{
									"Создать": function() 
									{
										loForm.submit();
									},
									"Отменить": function()
									{
										$( this ).dialog( "close" );
									}
								}
							});*/
							.reveal();
						});

						poThis.oActionPane.$self.append( 
							 poThis.button 
						);
					}
				},
				// Создание каталога
				// -----------------
				makeDir: 
				{
					oActionPane : this,
					button: $('<a href="#" alt="Создать"></a>').button( {icons: { primary: "ui-icon-folder-collapsed" }, label: "Создать" } ),
					textInput: $('<input type="text" style="width:100px;"/>'),
					init: function ( poThis )
					{
						poThis.button
						.bind( 'click', poThis, function( poEvent )
						{
							var loThis = poEvent.data;
							var loActionPane = loThis.oActionPane;
							var loDirName = loThis.textInput;
							var lsFileName = loDirName.val();

							// Если имя папки прошло валидацию — всё ок
							if ( loActionPane.isValidFileName( lsFileName ) )
							{
								loActionPane.oFileList.oFileManager.makeDirectory(
									loActionPane.oFileList.oLocation.p + "/" + lsFileName,
									function (poData) 
									{
										// Если такая папка уже существует — сругаемся
										if ( !poData.r )
										{
											loActionPane.oActions.messagePane.showError( 'Не получается создать папку' );
										}else
										{
											loThis.resetFolderName();
											loActionPane.oFileList.oFileManager.oTreeView.oSelectedNode.refreshContents(); // Обновим дерево
											loActionPane.oFileList.refreshContents(); // Да и список файлов заодно
										}
									}, 
									loActionPane,
									loActionPane.oFileList.oFileManager
								);
							}else
							{
								// А если не прошло — будем ругаться!
								loActionPane.oActions.messagePane.showError( 'Неверное имя папки' );
							}
						});
						
						poThis.textInput
						.bind( 'focusin', poThis, function( poEvent )
						{
							console.log("focusin'");
						
							var loThis = poEvent.data;
							
							if ( loThis.getFolderName() == loThis.getFolderNameStub() )
								loThis.setFolderName("");
						})
						.bind( 'focusout', poThis, function( poEvent )
						{
							var loThis = poEvent.data;
							
							if ( loThis.getFolderName() == "" )
								loThis.resetFolderName();
						});

						poThis.oActionPane.$self.append( 
							$('<div></div>')
							.append( poThis.textInput )
							.append( poThis.button )
						);
						
						
						poThis.resetFolderName();
					},
					resetFolderName: function()
					{
						this.setFolderName( null );
					},
					setFolderName: function( psValue )
					{
						this.textInput.attr("value", (psValue == null) ? this.getFolderNameStub() : psValue );
					},
					getFolderNameStub: function()
					{
						return "Имя папки";
					},
					getFolderName: function()
					{
						return this.textInput.attr("value");
					}
				},
				// Удаление выделенных
				// -------------------
				deleteFiles:
				{
					oActionPane: this,
					button: $('<a href="#" alt="Удалить выбранные"></a>').button( {icons: { primary: "ui-icon-trash" }} ).hide(),
					init: function ( poThis )
					{
						poThis.oActionPane.$self.append( poThis.button );
					},
					setTitle: function( psTitle )
					{
						this.button.button("option", "label", psTitle);
					},
					show: function()
					{
						this.button.show();
					},
					hide: function()
					{
						this.button.hide();
					}					
				},
				// Сообщалка ошибок
				// ----------------
				messagePane:
				{
					oActionPane: this,
					messageBlock: $('<div class="dich-file-manager-action-list-message-pane"></div>'),
					init: function( poThis )
					{
						poThis.oActionPane.$self.append( poThis.messageBlock );
					},
					showError: function( psText )
					{
						this.messageBlock.html( psText ).show().fadeOut(4000);
					}
				}
			};

			// Инициализация действий			
			for( loAction in this.oActions)
			{
				var loActionObject = this.oActions[ loAction ];
				loActionObject.init( loActionObject );
			}
				
			this.bVisible = false;
		},
		refreshContents: function ( poItems )
		{
			var loLocation = this.oFileList.oLocation;

			if (this.oFileList.oFileManager.o.onNavigate != null)
				this.oFileList.oFileManager.o.onNavigate( loLocation == null ? null : loLocation );

			if ( poItems.length > 0 )
			{
				this.oActions.deleteFiles.setTitle( "(" + poItems.length + ")" );
				this.oActions.deleteFiles.show();
			}
			else
			{
				this.oActions.deleteFiles.hide();
			}
		},
		isValidFileName: function( psVal )
		{
			var loNamePattern = new RegExp( "^[.a-zA-Z\\-_]{1}\\w*$", "i");
			return (loNamePattern.test( psVal )); 
		}
	}
	
	// ***************************
	// dichFileManagerFileListItem
	// ***************************

	function dichFileManagerFileListItem( poFileList, poData, poEvents ) 
	{
		this.events = $.extend(	{
			onClicked: null,			// Item
			onDoubleClicked: null,		// Item
			doQueryItemInfo: null,		// Path, Callback, Item context
			onEnterItem: null,			// Item
			onLeaveItem: null			// Item
		}, poEvents );

		this.oFileList = poFileList;
		this.oData = null;
		this.bSelected = false;
		this.$self = null;

		this.init( poData );
	}

	dichFileManagerFileListItem.prototype = 
	{
		init: function( poData )
		{	
			this.applyData( poData );
		
			// Let's bind the default event
			
			if (this.oParent != null)
				this.oParent.oNodes.push(this);
		},
		
		// methods
		applyData: function( poData )
		{
			var lsClassName = "";
			var lsPath = "";
			var lsName = "";
			var lsAccess = "";
			var lsHtml = "";
			var lsSize = "";
		
			this.oData = poData;

			if ( this.$self == null )
			{
				this.$self = $( '<li><a href="#" rel=""></a></li>' );
				this.oFileList.$container.append( this.$self );
			}
			
			if (poData != null)
			{
				lsClassName = poData.d ? ( 'directory' ) : ( 'file ext_' + poData.e );
				lsPath = escape( poData.p );
				lsName = poData.n;
				lsAccess = poData.a;
				lsSize = poData.s;
				
				lsHtml = '' + lsName + '<span class="permissions">' + lsAccess + '</span><span class="size">' + lsSize + '</span>';
			}
			
			this.$self.find( "A" )
				.attr( "rel", lsPath )
				.attr( "href", poData.p )
				.attr( "class", lsClassName )
				.html( lsHtml );
				
			this.$self
				.bind( 'dblclick', 		this, function( event )	{ event.data.onDoubleClickedDefault.call( event.data, event.data ); return false; } )
				.bind( 'click', 		this, function( event ) { event.data.onClickedDefault.call( event.data, event.data ); return false; } )
				.bind( 'mouseenter', 	this, function( event ) { event.data.onEnterDefault.call( event.data, event.data ); return false; } )
				.bind( 'mouseleave', 	this, function( event ) { event.data.onLeaveDefault.call( event.data, event.data ); return false; } );
		},
		select: function( pbDoNotTouchFileList )
		{
			if (this.bSelected == true) return;
		
			this.$self.addClass('selected');
			this.bSelected = true;

			if (pbDoNotTouchFileList == true) 
				return;
				
			this.oFileList.oSelection.add( this );
		},
		deselect: function( pbDoNotTouchFileList )
		{
			if (this.bSelected != true) return;
			this.$self.removeClass('selected');
			this.bSelected = false;

			if (pbDoNotTouchFileList == true) 
				return;

			this.oFileList.oSelection.remove( this );
		},
		toggleSelect: function()
		{
			if (this.bSelected == true)
				this.deselect();
			else
				this.select();
		},
		// events
		onClickedDefault: function( poItem ) 
		{ 
			poItem.toggleSelect.call( poItem );
			
			if (this.events.onClicked != null) 
				this.events.onClicked( poItem );
		},
		onDoubleClickedDefault: function( poItem ) 
		{ 
			if (this.events.onDoubleClicked != null) 
				this.events.onDoubleClicked( poItem );
		},
		onEnterDefault: function( poItem ) 
		{ 
			if (this.events.onEnterItem != null) 
				this.events.onEnterItem( poItem );
		},
		onLeaveDefault: function( poItem ) 
		{ 
			if (this.events.onLeaveItem != null) 
				this.events.onLeaveItem( poItem );
		},
		doQueryItemInfo: function( psPath, peCallback, poContext ) 
		{ 
			if (this.events.doQueryItemInfo != null) 
				this.events.doQueryItemInfo( psPath, peCallback, poContext );
		}
	}	
	
	// ***********************
	// dichFileManagerTreeView
	// ***********************

	function dichFileManagerTreeView( poFileManager, poElement, poOptions, poEvents) 
	{
		this.o = poOptions;
		this.oFileManager = poFileManager;
		this.oSelectedNode = null;
		this.oRootNode = null;
		this.events = $.extend(	{
			onItemClicked: null,           // Node
			doQueryItems: null,            // Path, Callback, Node context
			onNodeSelection: null,         // Node, TreeView
			onNodeSelectionChanged: null   // OldNode, NewNode, TreeView
		}, poEvents );
		
		this.$self = $(poElement);
		this.init();
	}

	dichFileManagerTreeView.prototype = 
	{
		init: function()
		{	
			this.oRootNode = new dichFileManagerTreeViewNode( this, null, 
				{ n: '/', p: this.o.root, s: 0, e: '', d: true },
				{
					onClicked: this.events.onItemClicked,
					doQueryItems: this.events.doQueryItems == null ? this.doQueryItemsDefault : this.events.doQueryItems
				}
			);
			
			this.oRootNode.select();
		},
		
		// methods
		setSelectedNode: function( poItem )
		{
			if (poItem != null)
				if (poItem.oTreeView != this) return false;

			if ( this.events.onNodeSelection != null )	this.events.onNodeSelection( poItem, this );

			var loOldNode = this.oSelectedNode;

			if (loOldNode != poItem)
			{
				if ( loOldNode != null )
					loOldNode.deselect( true );

				this.oSelectedNode = poItem;
				
				if ( this.events.onNodeSelectionChanged != null )
					this.events.onNodeSelectionChanged( loOldNode, poItem, this );
			}
		},		
		doQueryItemsDefault: function( psPath, peCallback, poContext, poFileManager ) 
		{ 
		}
	}
	
	// ***************************
	// dichFileManagerTreeViewNode
	// ***************************

	function dichFileManagerTreeViewNode( poTreeView, poParent, poData, poEvents ) 
	{
		this.events = $.extend(	{
			onClicked: null,		// Node
			doQueryItems: null		// Path, Callback, Node context
		}, poEvents );

		this.oTreeView = poTreeView;
		this.oParent = poParent;
		this.oData = poData;
		this.oParentElement = (poParent == null) ? poTreeView.$self : poParent.$self.children("LI") ;
		this.oNodes = new Array();

		this.init();
	}

	dichFileManagerTreeViewNode.prototype = 
	{
		init: function()
		{	
			this.$self = $('<ul class="dich-file-manager-tree-item"><li><a class="directory collapsed" href="#" rel="' + escape(this.oData.p) + '">' + this.oData.n + '</a></li></ul>');
			this.oParentElement.append( this.$self );
			this.bNodesGot = false;
			this.bCollapsed = true;
			this.bWaiting = false;
			this.bSelected = false;
			
			// Let's bind the default event
			this.$self.find("A").bind( 'click', this, function( event ) { event.data.onClickedDefault.call( event.data, event.data ); return false; } );
			
			if (this.oParent != null)
				this.oParent.oNodes.push(this);
		},
		
		// methods
		select: function()
		{
			if (!this.bNodesGot)
				this.refreshContents( true );
			else
				if (this.bSelected)
					this.toggleExpandCollapse();

			if (this.bSelected == true) return;

			this.$self.children("LI").addClass('selected');
			
			this.oTreeView.setSelectedNode( this );
			this.bSelected = true;
		},
		deselect: function( pbDoNotTouchTreeView )
		{
			if (this.bSelected != true) return;

			this.$self.children("LI").removeClass('selected');
			this.bSelected = false;

			if (pbDoNotTouchTreeView == true) return;
			
			this.oTreeView.setSelectedNode( null );
		},
		collapse: function()
		{
			if (this.bCollapsed) return;

			this.$self.children("A").removeClass('expanded').addClass('collapsed');
			this.$self.children("LI").children("UL").slideUp( { duration: this.oTreeView.o.treeViewCollapseSpeed, easing: this.oTreeView.o.treeViewCollapseEasing } );
			
			this.bCollapsed = true;	
		},
		expand: function()
		{
			if (!this.bCollapsed) return;
			
			if ( this.oNodes.length != 0 )
			{
				this.$self.children("LI").children("UL").slideDown( { duration: this.oTreeView.o.treeViewExpandSpeed, easing: this.oTreeView.o.treeViewExpandEasing } ); 
				
				this.$self.children("A").removeClass('collapsed').addClass('expanded');
				this.bCollapsed = false;
			}
			else
			{ 
				this.$self.children("A").removeClass('expanded').addClass('collapsed');
				this.bCollapsed = true;	
			}
		},
		toggleExpandCollapse: function()
		{
			if (this.bCollapsed) 
				this.expand();
			else 
				this.collapse();
		},
		refreshContents: function( pbNeedExpand )
		{
			this.setWaiting( true );
			
			var lsPath = this.oData.p;

			if (lsPath != '/')
				lsPath += '/';
				
			this.doQueryItemsDefault( 
				lsPath, 
				function (poData) 
				{
					this.bNodesGot = true;
					this.setWaiting( false );
				
					if (poData != null)
					{
						this.clearNodes();
					
						var loData = null;
						for ( var i = 0; i < poData.length; ++i)
						{
							loData = poData[i];

							if (loData.d)
								new dichFileManagerTreeViewNode( this.oTreeView, this, loData, this.events );
						}
					
						if ( pbNeedExpand == true )
							this.expand();
					}
				}, 
				this,
				this.oTreeView.oFileManager
			);
		},
		clearNodes: function()
		{
			this.oNodes = new Array();
			this.$self.children("LI").children("UL").children("LI").remove();
		},
		
		// privates
		setWaiting: function( pbState )
		{
			if (pbState)	this.$self.children("A").addClass('wait');
			else			this.$self.children("A").removeClass('wait');

			this.pbWaiting = pbState;
		},
		
		// events
		onClickedDefault: function( poNode ) 
		{ 
			poNode.select.call( poNode );
			
			if (this.events.onClicked != null) 
				this.events.onClicked( poNode );
		},
		doQueryItemsDefault: function( psPath, peCallback, poContext, poFileManager ) 
		{ 
			if (this.events.doQueryItems != null) 
				this.events.doQueryItems( psPath, peCallback, poContext, poFileManager );
		}
	}

})(jQuery);
