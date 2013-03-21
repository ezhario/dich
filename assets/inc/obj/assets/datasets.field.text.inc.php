<?
class DatasetFieldClassText extends DatasetFieldClass
{
	public function DatasetFieldClassText( $poDatasetField, $poDataAdapter )
	{
		DatasetFieldClass::DatasetFieldClass( $poDatasetField, $poDataAdapter );
	}
	
	protected function get_Value() 
	{ 
		return $this->_oDataAdapter->Entity->Get( $this->Name ); 
	}
	
	protected function set_Value( $psValue ) 
	{ 
		$this->_oDataAdapter->Entity->Set( $this->Name, stripslashes($psValue) ); 
	}
	
	public function DrawEditFormPart()
	{
		return( Templates::Macro( "assets/field.text.edit", array( "field" => $this ) ) );
	}
	
	public function ReceiveEditFormPart( $paSource )
	{
		$lsHash = $this->Hash;
	
		$lsValue = $paSource[ $lsHash ];
		
		$this->Value = ($lsValue == null) ? "" : $lsValue;
	}
}

Datasets :: RegisterFieldClass( "DatasetFieldClassText", "Текстовое поле" );
?>
