<? 
	$result = Net::Url()->Compare(Net::Url( $this->Get("href") )); 
	$warn =$this->Get("warning") == true;
	
	if (@@skip_auto_navigation@@ == true)
		$result = 0;
?>

{{if $result == "2" }}<li><span class="dich-main-menu-item-current{{if %%warn%% }} dich-main-menu-item-warning{{endif}}">{{ @@title@@ }}</span></li>{{endif}}
{{if $result == "1" }}<li><a {{if @@id@@!=null}}id="{{ @@id@@ }}"{{endif}} class="dich-main-menu-item-current{{if %%warn%% }} dich-main-menu-item-warning{{endif}}" href="{{@@href@@}}">{{ @@title@@ }}</a></li>{{endif}}
{{if ($result == "0") || ($result == "3") }}<li><a {{if @@id@@!=null}}id="{{ @@id@@ }}"{{endif}} href="{{@@href@@}}"{{if %%warn%% }} class="dich-main-menu-item-warning"{{endif}}>{{ @@title@@ }}</a></li>{{endif}}
