<?
class DatasetFieldClassDHTML extends DatasetFieldClassText
{
	public function DatasetFieldClassDHTML( $poDatasetField, $poDataAdapter )
	{
		DatasetFieldClassText::DatasetFieldClassText( $poDatasetField, $poDataAdapter );
	}
	
	public function DrawEditFormPart()
	{
		return( Templates::Macro( "assets/field.dhtml.edit", array( "field" => $this ) ) );
	}
}

Datasets :: RegisterFieldClass( "DatasetFieldClassDHTML", "Текстовое поле с DHTML-редактором" );
?>
