<?
class StructureLogicClassRedirector extends StructureLogic
{
	public function StructureLogicClassRedirector( $poContent )
	{
		StructureLogic::StructureLogic( $poContent );
	}

	/****************************************
		Аксессоры
	*****************************************/
	
	protected function get_RedirectionDestination()
	{
		return $this->Settings["raw_redirect_link"];
	}
	
	/****************************************
		Шрутизация
	*****************************************/

	public function RouteSettingsEditing( $psLevel1 )
	{
		if ($psLevel1 == "save")
		{
			$loSettings = $this->Settings;
		
			$loSettings["raw_redirect_link"] = Net::PostResult("raw_redirect_link");
			$this->Settings = $loSettings;
		
			$this->SaveSettings();
		
           	Net::Redirect( Net::URL( Router::GetEffectivePath() ) );
			return true;
		}
	
		// Заполним аргументы макроса
		$laArgs = array( "value" => $this, "content" => $this->Content );

		// И вернём результат выполнения макроса
		Templates::Output( Templates::Code( Templates::Macro("assets/logic.redirector.page.config", $laArgs) ) );
		return true;
	}
		
	public function IsEditingSupported()
	{
		return false;
	}
	
	public function RouteContentRendering()
	{
		// У страницы-редиректора нет локальной навигации. усеницы, червиё и мракобесие это всё.
		if (func_num_args() != 0)
			return false;
	
		// Отредиректим. К сожалению, ядру это управления не передаст.
       	Net::Redirect( Net::URL( Router::GetEffectivePath() ) -> Set( $this->RedirectionDestination ) );
	
		return true;
	}
}

Structure :: RegisterLogicClass( "StructureLogicClassRedirector", "Перенаправитель" );
?>
