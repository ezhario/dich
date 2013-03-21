<?
	class	ErrorsController
	{
	    public static function ResizeImageOnDemand()
	    {
	        // Если GD не установлен - нам больше делать нечего
	        if (!extension_loaded('gd') && !extension_loaded('gd2'))
	            return false;
	    
	        // Будем из строки маршрутизации регекспом вырезать имя картинки.
	        // Поддерживаются расширения .png, .jpg и .jpeg.
	        $lsImpliedImagePath = Router::GetRoutingString();
	        $lsExtractFileNameRegExp = "/^(.*\/)(.*\.png|.*\.jpg|.*\.jpeg)\/$/";
	        $laMatches = array();

	        $lnCount = preg_match($lsExtractFileNameRegExp, $lsImpliedImagePath, $laMatches);

            // Если есть матчи, то работаем дальше.
	        if ( $lnCount >= 1 )
	        {
	            // Разберём имя запрашиваемой картинки, достанем оригинальное имя,
	            // ыкстеншон и размеры.
	            $lsMatchedImagePath = $laMatches[1];
	            $lsMatchedImageName = $laMatches[2];
                $lsFinalRegExp = "/^(.*)_([0-9]+)x([0-9]+)(\..*)$/";
                $laFinalMatches = array();
	            
	            $lnCount = preg_match($lsFinalRegExp, $lsMatchedImageName, $laFinalMatches);
	            
	            // И снова - если есть матчи, то работаем дальше.
	            if ( ($lnCount >= 1) && (count($laFinalMatches) == 5) )
	            {
	                $lsSourceAbsoluteFileName = FileInfo::GetDocumentRoot() . $lsMatchedImagePath . $laFinalMatches[1] . $laFinalMatches[4];
	                $lnDesiredWidth = $laFinalMatches[2];
	                $lnDesiredHeight = $laFinalMatches[3];
	                $lsDestinationURI = $lsMatchedImagePath . $laFinalMatches[1] . "_" . $lnDesiredWidth . "x" . $lnDesiredHeight . $laFinalMatches[4];
	                $lsDestinationAbsoluteFileName = FileInfo::GetDocumentRoot() . $lsDestinationURI;

                    // Проверим размеры картинки	                
	                if ( ($lnDesiredWidth > 0) && ($lnDesiredWidth <= 300) && ($lnDesiredHeight > 0) && ($lnDesiredHeight <= 300) )
	                {
	                    // Проверим, есть ли такая картинка?
	                    $loFileInfo = new FileInfo($lsSourceAbsoluteFileName);
	                    
	                    if ( !$loFileInfo->IsCorrupted )
	                    {
	                        $loSourceGD2Image = @imagecreatefromstring(file_get_contents($lsSourceAbsoluteFileName));
	                        $lnSourceWidth = imagesx( $loSourceGD2Image );
	                        $lnSourceHeight = imagesy( $loSourceGD2Image );
	                        
	                        $loDestinationGD2Image = @imagecreatetruecolor( $lnDesiredWidth, $lnDesiredHeight );
	                        
	                        imagealphablending( $loDestinationGD2Image, false );
	                        imagesavealpha( $loSourceGD2Image, true );
	                        imagesavealpha( $loDestinationGD2Image, true );
	                        
	                        $loTransparentColor = imagecolorallocatealpha( $loDestinationGD2Image, 255, 255, 255, 127 );
	                        imagefilledrectangle( $loDestinationGD2Image, 0, 0, $lnDesiredWidth, $lnDesiredHeight, $loTransparentColor );

                            $lnDestinationX = 0;
                            $lnDestinationY = 0;
                            $lnDestinationWidth = $lnDesiredWidth;
                            $lnDestinationHeight = $lnDesiredHeight;

                            if ( ($lnSourceWidth != 0) && ($lnSourceHeight))
                            {
	                            $lfAspectX = ($lnDestinationWidth * 1.0) / ($lnSourceWidth * 1.0);
	                            $lfAspectY = ($lnDestinationHeight * 1.0) / ($lnSourceHeight * 1.0);
	                            $lfSourceAspect = ($lnSourceWidth * 1.0) / ($lnSourceHeight * 1.0);
	                            
	                            if ($lfAspectX > $lfAspectY)
	                            {
	                                $lnDestinationWidth = ($lnDesiredHeight * 1.0) * $lfSourceAspect;  
	                                $lnDestinationX = ($lnDesiredWidth - $lnDestinationWidth) / 2;
	                            }
	                            
	                            if ($lfAspectY > $lfAspectX)
	                            {
	                                $lnDestinationHeight = ($lnDesiredWidth * 1.0) / $lfSourceAspect;  
	                                $lnDestinationY = ($lnDesiredHeight - $lnDestinationHeight) / 2;
	                            }
	                        }
	                        
	                        imagecopyresampled( $loDestinationGD2Image, $loSourceGD2Image,
	                            $lnDestinationX, $lnDestinationY, 0, 0, 
	                            $lnDestinationWidth, $lnDestinationHeight, $lnSourceWidth, $lnSourceHeight
	                        );
	                        
	                        $lbResult = true;
	                        
                            if ( $loFileInfo->Extension == "png" )
                                $lbResult = imagepng( $loDestinationGD2Image, $lsDestinationAbsoluteFileName );
                            else
                                $lbResult = imagejpg( $loDestinationGD2Image, $lsDestinationAbsoluteFileName );
                                
                            @chmod( $lsDestinationAbsoluteFileName, 0777 );
                            
                            imagedestroy( $loSourceGD2Image );
                            imagedestroy( $loDestinationGD2Image );

                            if ( $lbResult )
                				Net::Redirect( $lsDestinationURI );
                       
	                        return $lbResult;
	                    }
	                }
	            }
	        }
	    
	        return false;
	    }
	
		public static function Show404()
		{
			Templates::Output( Templates::Code( Templates::Macro("404") ) );
			return true;
		}
		
		public static function ShowSiteLock()
		{
			if (Users::AllowSiteEntering())
				return false;
			
			$lsValue = Net::PostResult("unlock");

			if (isset($lsValue))
			{
				Users::TryToUnlockSiteEntering( $lsValue );
				
				Net::Redirect( Net::URL( Router::GetRoutingString() ) );
				return true;
 			}
			
			Templates::Output( Templates::Code( Templates::Macro("sitelock") ) );
			return true;
		}
	}
?>
