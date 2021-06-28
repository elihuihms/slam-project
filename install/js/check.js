function checkGeneralForm()
{
	var url="./ext/check_gen.php";
	var arg= "SLAM_CONF_PREFIX="+document.getElementById('SLAM_CONF_PREFIX').value;
	arg += "&SLAM_CONF_PATH="+encodeURIComponent(document.getElementById("SLAM_CONF_PATH").value);
	showPopupDiv( "checkGeneral", url, arg, []);	
}

function checkDatabaseForm()
{
	var url="./ext/check_db.php";
	var arg= "SLAM_DB_HOST="+encodeURIComponent(document.getElementById("SLAM_DB_HOST").value);
	arg += "&SLAM_DB_PORT="+encodeURIComponent(document.getElementById("SLAM_DB_PORT").value);
	arg += "&SLAM_DB_NAME="+encodeURIComponent(document.getElementById("SLAM_DB_NAME").value);
	arg += "&SLAM_DB_CHARSET="+encodeURIComponent(document.getElementById("SLAM_DB_CHARSET").value);
	arg += "&SLAM_DB_USER="+encodeURIComponent(document.getElementById("SLAM_DB_USER").value);
	arg += "&SLAM_DB_PASS="+encodeURIComponent(document.getElementById("SLAM_DB_PASS").value);

	showPopupDiv("checkGeneral", url, arg, []);
}

function checkFilesForm()
{
	var url="./ext/check_file.php";
	var arg= "SLAM_FILE_ARCH_DIR="+encodeURIComponent(document.getElementById("SLAM_FILE_ARCH_DIR").value);
	arg += "&SLAM_FILE_TEMP_DIR="+encodeURIComponent(document.getElementById("SLAM_FILE_TEMP_DIR").value);
	
	showPopupDiv("checkGeneral", url, arg, []);
}

function showPopupDiv( id, url, arg, opt )
{
	var sDiv=document.createElement('div');
	sDiv.setAttribute('id',id);
	sDiv.setAttribute('xml-url',url);

	if (opt['top'] != null)
		sDiv.style.top=opt['top'];
	if (opt['left'] != null)
		sDiv.style.left=opt['left'];
	if (opt['bottom'] != null)
		sDiv.style.bottom=opt['bottom'];
	if (opt['right'] != null)
		sDiv.style.right=opt['right'];
	
	document.body.appendChild(sDiv);
	
	if (window.XMLHttpRequest)
	{
		http = new XMLHttpRequest();
		http.url = url;
		http.open('POST', url, true);
		http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

		http.onreadystatechange = function() {
			if(http.readyState == 4 && http.status == 200) {
				sDiv.innerHTML = http.responseText;

				if (opt['noclose'] == null)
					sDiv.innerHTML = sDiv.innerHTML+"<a id='popupDivCloseA' onClick=\"removeBodyId('"+id+"')\">close</a>";

			}else{
				sDiv.innerHTML = http.statusText;
			}
		}

		http.send(arg);
	}
	else
		sDiv.innerHTML = "Sorry, your browser does not support XMLHTTPRequest objects.";	

	/* return false to abort link redirection */
	return false;
}

function removeBodyId( id )
{
	var hDiv = document.getElementById( id );
	document.body.removeChild(hDiv);
	return;
}