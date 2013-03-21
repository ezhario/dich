<?
class DatasetFieldClassBigText extends DatasetFieldClassText
{
	public function DatasetFieldClassBigText( $poDatasetField, $poDataAdapter )
	{
		DatasetFieldClassText::DatasetFieldClassText( $poDatasetField, $poDataAdapter );
	}
	
	public function DrawEditFormPart()
	{
		return( Templates::Macro( "assets/field.bigtext.edit", array( "field" => $this ) ) );
	}
}

Datasets :: RegisterFieldClass( "DatasetFieldClassBigText", "Многострочное текстовое поле" );
?>
