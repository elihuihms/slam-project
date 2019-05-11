<form name='userActions' action='' method='POST'>
	<input type='button' value='Preferences' onClick="showPopupDiv('pub/user_prefs.php','userActionPopup',{'noclose':true})"/>
	<input type='button' value='Change Password' onClick="showPopupDiv('pub/password_change.php','userActionPopup',{'noclose':true})"/>
<?php
if( $_REQUEST['superuser'] == 'true'){
	echo("	<input type='button' value='User Tools' onClick=\"showPopupDiv('pub/user_tools.php','userActionPopup',{'noclose':true})\"/>\n");
	echo("	<input type='button' value='Edit Database' onClick=\"window.open('admin.php')\"/>\n");
}
?>	
</form>
<br />
<form name='logoutForm' action='index.php' method='POST'>
	<input type='hidden' name='logout' value='true' />
	<input type='submit' value='Log Out' />
</form>