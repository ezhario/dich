<?
	class Log
	{
		private static $_sLogText = "";
		private static $_nStartTime = "";
		
		public static function Init()
		{
			Log::$_nStartTime = microtime();
		}		
		
		public static function RunTime()
		{
			return microtime() - Log::$_nStartTime;
		}
	
		public static function PrintF()
		{
			$loArgs = func_get_args();
			$lsOutput = call_user_func_array("sprintf", $loArgs);
		
			if ($lsOutput != "")
				Log::Output($lsOutput);
		}
	
		private static function Output( $psString )
		{
			Log::$_sLogText .= date("Y-m-d H:i:s", time()).": ".$psString."<br />\n";
		}
	
		public static function Contents()
		{
			return Log::$_sLogText;
		}
		
		public static function HandleError($pnErrNo, $psErrStr, $psErrFile, $pnErrLine)
		{
			$lbTerminate = false;
		
			 switch ($pnErrNo) {
				case E_USER_ERROR:
				case E_ERROR:
					Log::PrintF("%s", "<b>ERROR</b> [$pnErrNo] $psErrStr");
					Log::PrintF("%s", "Fatal error on line $pnErrLine in file $psErrFile");
					Log::PrintF("%s", "Terminating");
					
					$lbTerminate = true;
					break;

				default:
					Log::PrintF("%s", "<b>SOME WARNING</b> [$pnErrNo on line $pnErrLine in file $psErrFile] $psErrStr");
					break;
				}
			
			if ($lbTerminate)
			{
				while (@ob_end_clean()) {}
				print( Log::Contents() );
			
				exit(1);
			}
		}
	}

	set_error_handler("Log::HandleError");
	Log::Init();
?>
