function checkAll(theForm, cName, allNo_stat) {
	var n=theForm.elements.length;
	for (var x=0;x<n;x++){
		if (theForm.elements[x].className.indexOf(cName) !=-1){
			if (allNo_stat.checked) {
			theForm.elements[x].checked = true;
		} else {
			theForm.elements[x].checked = false;
		}
	}
	}
}

  function confirm_del_prompt(URL) {
	if (!confirm("Do you really want to delete the backup file?")) 
		return false;	  
	window.location = URL;
	}

 function confirm_rest_prompt(URL) {
	if (!confirm("Do you really want to restore the database from backup file? Current database will be lost. \nAfter confirming 'OK', please be patient. Restore with a large backup file may take a long time. . .")) 
		return false;	  
	window.location = URL;
	}
