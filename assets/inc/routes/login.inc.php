<?
	class	LoginController
	{
		public static function LoginPage( $psLevel1 = "")
		{
			/* Пытаемся залогиниться */ if ( $psLevel1 == "login" )
			{
				Users::Login(Net::PostResult("login"), Net::PostResult("password"));
				Net::Redirect( Net::URL( Router::GetEffectivePath() ) );
			}
			
			if ($psLevel1 != "")
				Net::Redirect( Net::URL( Router::GetEffectivePath() ) );

			Templates::Output( Templates::Code(Templates::Macro("login")) );
			return true;
		}
	}
?>
