<?

class FileInfo extends PropertyObject
{
	protected $_sFileName = "";
	protected $_aPathInfo = null;

	public static function FormatSize( $pnSizeInBytes )
	{
		$laSizes = I18n::Get("byte.size.grades");
		
		if ( $pnSizeInBytes == 0 ) 
			return $laSizes[0];
		
		$lnGrade = floor( log( $pnSizeInBytes, 1024) );
		$lnComputedSize = round( $pnSizeInBytes / pow( 1024, $lnGrade ) , $lnGrade > 0 ? 2 : 0 );
		
		return $lnComputedSize . " " . $laSizes[ $lnGrade + 1 ];
	}
	
	public static function IsExtensionAnImageOne( $psExtension )
	{
		return 
		    ($psExtension == "gif") || ($psExtension == "jpg") || ($psExtension == "jpeg") || ($psExtension == "png") ||
		    ($psExtension == ".gif") || ($psExtension == ".jpg") || ($psExtension == ".jpeg") || ($psExtension == ".png")
		;
	}
	
	public static function GetDocumentRoot()
	{
	    return Config::$System["documentroot"];
	}
		
	public function FileInfo( $psFileName )
	{
		$this->_sFileName = $psFileName;
		$this->_aPathInfo = pathinfo( $this->FullName );
		$this->_aPathInfo["error"] = file_exists( $this->FullName ) ? 0 : 1;
	}
	
	protected function get_FullName ()				{ return $this->_sFileName; }
	protected function get_Path ()					{ return @$this->_aPathInfo["dirname"]; }
	protected function get_Extension ()				{ return @$this->_aPathInfo["extension"]; }
	protected function get_Name ()					{ return @$this->_aPathInfo["basename"]; }
	protected function get_NameWithoutExtension ()	{ return @$this->_aPathInfo["filename"]; }
	protected function get_Size ()		
	{ 
		$lnFileSize = @filesize( $this->FullName ); 
		return ($lnFileSize == FALSE) ? 0 : $lnFileSize;
	}
	protected function get_IsImage ()			
	{ 
		$lsExt = strtolower($this->Extension);
		
		return FileInfo::IsExtensionAnImageOne( $lsExt );
	}
	protected function get_IsCorrupted ()		{ return $this->_aPathInfo[ "error" ] != 0; }	
	protected function get_URL ()				{ return str_replace( FileInfo::GetDocumentRoot(), "", $this->FullName); }
}

class UploadedFileInfo extends FileInfo
{
	protected $_aUploadedFileInfo = null;
	protected $_sUploadedFrom = "";

	public function UploadedFileInfo ( $paUploadedFileInfo )
	{
		$this->_aUploadedFileInfo = $paUploadedFileInfo;	// Сохраним информацию о файле
		
		$this->_sUploadedFrom = I18n::UniversalTransliterateFilename( $this->_aUploadedFileInfo[ "name" ] );
		
		$this->FileInfo( $this->UploadedFromName );			// Обманем родителя - подсунем ему для инициализации информацию о загруженном файле
		$this->_sFileName = $this->TemporaryName;			// И ещё раз его обманем - он будет считать, что информация для этого файла
	}
	
	protected function get_Size ()				{ return $this->_aUploadedFileInfo[ "size" ]; }
	protected function get_IsCorrupted ()		{ return $this->_aUploadedFileInfo[ "error" ] != 0; }	

	// Добавлено
	protected function get_TemporaryName ()		{ return $this->_aUploadedFileInfo[ "tmp_name" ]; }
	protected function get_UploadedFromName ()	{ return $this->_sUploadedFrom; }
	protected function get_MimeType ()			{ return $this->_aUploadedFileInfo[ "type" ] ; }
}	

class Files
{
	public static function Store( FileInfo $poInfo, $psStoreTo = null )
	{
		if ($poInfo == null)		return null;
		if ($poInfo->IsCorrupted)	return null;
		
		// Выберем тип файла
		$lsFileClassFolder = $poInfo->IsImage ? "images" : "files";

		// Достанем имя файла		
		$lsFileName = $poInfo->NameWithoutExtension;
		if ($lsFileName == "")
			$lsFileName = "unnamed";
		
		// Достанем расширение
		$lsExtension = $poInfo->Extension;
		if ($lsExtension != "")
			$lsExtension = "." . $lsExtension;

		// Скомпонуем имя целиком
		$lsCompositeName = implode("/", array(
			Config::$System["uploadspath"],
			$lsFileClassFolder,
			$lsFileName[0],
			$lsFileName . "-" . date('Ymd') . "-" . date('His') . $lsExtension 
		));
		
		// Создадим каталоги в пути
		$lnOldUMask = umask(0);
		@mkdir( dirname( $lsCompositeName ), 0755, TRUE );
		umask($lnOldUMask);
		// Перекинем файл к себе
		@copy( $poInfo->FullName, $lsCompositeName );
		
		// Вернём результат
		return file_exists($lsCompositeName) ? new FileInfo( $lsCompositeName ) : null;
	}
	
	public static function GetDirectoryListing( $psDirectory = "", $psRoot = "" )
	{
		$lsDirectory = ($psDirectory == "") ? "/" : $psDirectory;
		$lsRoot = ($psRoot == "") ? Config::$System["documentroot"] : $psRoot;
		$lsRealPath = $lsRoot . $lsDirectory;
		$laResults = array();

		if ( file_exists( $lsRealPath ) )
		{
			$laFiles = scandir( $lsRealPath );
			natcasesort( $laFiles );
			
			if ( count($laFiles) > 2 )
			{
				$laDirList = array();
				$laFileList = array();
				
				foreach ($laFiles as $lsFile)
					if ( ($lsFile != ".") && ($lsFile != "..") )
					{
						$lsRealFileName = $lsRealPath . $lsFile;
						
						if ( file_exists( $lsRealFileName ) )
						{
							$lsPermissions = decoct( fileperms( $lsRealFileName ) & 511 );
						
							if ( is_dir( $lsRealFileName ) )
								$laDirList [] = array( "n"=>$lsFile, "p"=>$lsDirectory.$lsFile, "s"=>"", "e"=>"", "d"=>true, "ro"=>false, "i"=>false, "a"=> $lsPermissions );	
							else
							{
								$loFileInfo = new FileInfo( $lsRealFileName );
								$laFileList [] = array( "n"=>$lsFile, "p"=>$lsDirectory.$lsFile, "s"=>FileInfo::FormatSize( $loFileInfo->Size ), "e"=>$loFileInfo->Extension, "d"=>false, "ro"=>false, "i"=>$loFileInfo->IsImage, "a"=>$lsPermissions );	
							}
						}
					}
					
				$laResults = array_merge($laDirList, $laFileList);
			}
		}
		
		return $laResults;
	}
	
	public static function MakeDirectory( $psDirectory = "", $psRoot = "" )
	{
		$lsDirectory = ($psDirectory == "") ? "/" : $psDirectory;
		$lsRoot = ($psRoot == "") ? Config::$System["documentroot"] : $psRoot;
		$lsRealPath = $lsRoot . $lsDirectory;
		$laResults = array();

		$lbResult = true;

		if ( file_exists( $lsRealPath ) )
		{
			$lbResult = false;
		}
		else
		{
			$lbResult = @mkdir( $lsRealPath );
		}

		$laResults["r"] = $lbResult;

		return $laResults;
	}
	
	public static function IsFileInBlackList( $psFile, $psRoot = "" )
	{
	}
}

?>
