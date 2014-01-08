var easyCrudPath = "";
var actualValue = null;
var runningRequest = false;
var request;
var globalCounter = 0;

/**
 * Alias for the document.getElementById function.
 * @param id ID of the HTML element been searched.
 */ 
function ge(id){
	return document.getElementById(id);
}


/**
 * Changes the table FIRST ROW content to the Loading GIF
 * @param table Name of the table
 */ 
function startLoadingImage(table){	
	var tab = ge(table+'_table');
	tab.rows[0].firstChild.innerHTML = "<img src=\'" + easyCrudPath + "css/img/loading2.gif\' ></img>";	
}

/**
 * Sends an AJAX request to make an insertion on a database table.
 * @param url URL that will receive the AJAX REQUEST (search database request)
 * @param form HTML FORM element that contains the fields used to create the new row.
 * @param table Table been searched 
 * @param div HTML DIV element that will receive the response.
 */ 
function sendInsertRequest(url,form,table,div){
	startLoadingImage(table);
	var params = getFormParams(ge(form));
	params = "action=INSERT&table="+table + params;
	runningRequest = true;
	request = $.ajax({
		type: "GET",
		url: url,
		data: params,
		success: function(msg){	
			runningRequest = false;	 		
			getRefreshedTableRequest(url,table,div);
		}
	});	
	
}

/**
 * Shows a JQuery Confirmation Dialog. IF confirmed, sends
 * a Deletion request.
 * @param url URL that will receive the AJAX REQUEST (search database request)
 * @param table Table been searched
 * @param params Primary Keys values of the row being deleted
 * @param div HTML DIV element that will receive the response.
 */ 
function showDeleteConfirmation(url,table,params,div){
	$(function() {
		$( "#deleteConfirm_"+table ).dialog({
			resizable: false,
			height:160,
			modal: true,
			dialogClass: 'deleteAlert',
			buttons: {
				"Delete": function() {
					$( this ).dialog( "close" );
					sendDeleteRequest(url,table,params,div);
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			}
		});	
	});		
}

/**
 * Sends an AJAX request to make a deletion on a database table.
 * @param url URL that will receive the AJAX REQUEST (search database request)
 * @param table Table been searched
 * @param params Primary Keys values of the row being deleted
 * @param div HTML DIV element that will receive the response. 
 */
function sendDeleteRequest(url,table,params,div){
	
	var parameters = "action=DELETE&table=" + table + "&pks=" + params;
	
	startLoadingImage(table);
	runningRequest = true;
	request = $.ajax({
		type: "GET",
		url: url,
		data: parameters,
		success: function(msg){	 
			runningRequest = false;		
			getRefreshedTableRequest(url,table,div);
		}
	});	
	
}

/**
 * Sends an AJAX request to make a search on a database table.
 * @param url URL that will receive the AJAX REQUEST (search database request)
 * @param table Table been searched
 * @param criteria SQL Criteria used for searching the table.
 * @param columnOrder SQL Order by criteria. ex: order by "name"
 * @param changeOrder boolean that indicates if the search should change from ASC to DESC and vice-versa
 * @param pageNumber Pagination parameter. Number of the page to GET.
 * @param div HTML DIV element that will receive the response. 
 */
function sendSearchRequest(url,table,criteria,columnOrder,orderType,pageNumber,div){	
	
	var params = "action=ORDER&table="+table + "&orderColumn="+columnOrder + "&searchCriteria="+criteria+"&pageNumber="+pageNumber+"&orderType="+orderType;	
	startLoadingImage(table);
	runningRequest = true;
	request = $.ajax({
		type: "GET",
		url: url,
		data: params,
		success: function(msg){ 						
			runningRequest = false;
			getRefreshedTableRequest(url,table,div);
		}
	});	

}

/**
 * Updates the inner HTML of a DIV with the table code
 * requested via ajax.
 * @param url URL that will receive the AJAX REQUEST
 * @param table Table been requested
 * @param div Div that will receive the table code
 */
function getRefreshedTableRequest(url,table,div){
	var params = "printTable=TRUE&table="+table;
	runningRequest = true;
	request = $.ajax({
		type: "GET",
		url: url,
		data: params,
		success: function(answer){			
			runningRequest = false;	
			ge(div).innerHTML = answer;
		}
	});	
}


/**
 * Changes the visibility of a 
 * insert form, using the table name.
 * @param table Name of the table
 */ 
function toggleInsertForm(table){
	var obj = ge(table+"_insertDiv");
	var display = obj.style.display;
	if( display == 'none'){
		obj.style.display = 'block';
	}else{
		obj.style.display = 'none';
	}
}

/**
 * Changes the visibility of a 
 * search form, using the table name.
 * @param table Name of the table
 */ 
function toggleSearchForm(table){
	var obj = ge(table + "_searchDiv");
	var display = obj.style.display;
	if( display == 'none'){
		obj.style.display = 'block';
	}else{
		obj.style.display = 'none';
	}
}

/**
 * Changes the visibility of an element
 * @param elementID ID of the HTML element.
 * 
 */ 
function changeVisibility(elementID){
	var obj = ge(elementID);
	var display = obj.style.display;
	if( display == 'none'){
		obj.style.display = 'block';
	}else{
		obj.style.display = 'none';
	}
}

/**
 * Iterates over a HTML FORM Element to build a GET
 * request parameter. Ex: &name=Bob&age=34
 * 
 * @param formX HTML FORM element that contains the fields that will build the REQUEST
 * @return String containing the request parameters
 */
function getFormParams(formX){
	var returnString = "";	
	for(var i = 0; i < formX.elements.length; i++){
		var element = formX.elements[i];
		if(element.type == "text" || element.type == 'password'){
			returnString += "&"	 + element.name + "=" + element.value;
		}else if(element.type == "file"){			
		}
	}
	return returnString;
}

/**
 * Function responsible for the AUTO-SEARCH.
 * This function is used to set a timout, so the autosearch is not
 * buggy and instant search. The default search timeout is 700 ms.
 * The autosearch only starts when theres no Char typed for at least 700ms.
 * If the user types too fast, it will only search at the end of the typing.
 * 
 * @param formName name of the HTML FORM element that contains the fields
 * 				   responsible for the search parameters. Those fields in the FORM
 * 					will generate the CRITERIA for the DATABASE search.
 * 
 * @param url URL that will receive the AJAX REQUEST (search database request)
 * 
 * @param table Name of the database table that is been searched.
 * 
 * @param element HTML element that has been changed and is requesting an autosearch.
 * 
 * @param readonly boolean value. When true, it specifies an FK Table Search. When false, is a common search.
 * 
 * @param fieldName Used only on FKSearchs (readonly = true). It tells the name of the HTML Input Element
 * 					that will receive the foreign key value, when the user clicks on a table ROW.
 * 
 * @param columnName name of the column that is Primary Key of the searched table. This is the column that
 * 					will fill the element "fieldName" when the user clicks on a ROW.
 * 
 * @param divName name of the HTML DIV Element that will receive the request response, eg. the TABLE code after
 * 					the search.
 * 
 */
function startSearchTiming(formName,url,table,element,readonly,fieldName,columnName,divName){
	globalCounter++;
	setTimeout("tryToSearch('"+globalCounter+"','"+formName+"','"+url+"','"+table+"','"+element +"','"+readonly+"','"+fieldName+"','"+columnName+"','"+divName+"')", 700);
}

/**
 * Works together with "startSearchTiming".
 * This function tries to search, but it will only work, if the counter is matching with the global
 * counter value. This way it avoids multiple searchs. The search will only work for the last typed
 * character (the one who waited 700ms with no other char typed after it)
 * 
 * @param counter the value of the counter at the moment of the typing
 * 
 * @param formName name of the HTML FORM element that contains the fields
 * 				   responsible for the search parameters. Those fields in the FORM
 * 					will generate the CRITERIA for the DATABASE search.
 * 
 * @param url URL that will receive the AJAX REQUEST (search database request)
 * 
 * @param table Name of the database table that is been searched.
 * 
 * @param element HTML element that has been changed and is requesting an autosearch.
 * 
 * @param readonly boolean value. When true, it specifies an FK Table Search. When false, is a common search.
 * 
 * @param fieldName Used only on FKSearchs (readonly = true). It tells the name of the HTML Input Element
 * 					that will receive the foreign key value, when the user clicks on a table ROW.
 * 
 * @param columnName name of the column that is Primary Key of the searched table. This is the column that
 * 					will fill the element "fieldName" when the user clicks on a ROW.
 * 
 * @param divName name of the HTML DIV Element that will receive the request response, eg. the TABLE code after
 * 					the search.
 */
function tryToSearch(counter,formName,url,table,element,readonly,fieldName,columnName,divName){
	
	if( counter == globalCounter && actualValue != element.value){
		var crit = generateCriteria(formName); 
		if(readonly == 'true'){	
			sendFKSearchRequest(url,fieldName,table,columnName,crit,'','',1,divName,true);			
		}else{
			sendSearchRequest(url,table,crit,'','','1',divName);
		}
	}
}

/**
 * Function the generates a DATABASE search criteria. 
 * 
 * @param formName name of the HTML FORM element that contains the fields used for the search.
 * 
 * @return A String containing the search criteria
 * 
 */ 
function generateCriteria(formName){
	var formX = ge(formName);
	var crit = "";
	for(var i = 0; i < formX.elements.length; i++){
		var element = formX.elements[i];
		if(element.type == "text" && element.readOnly == false){
			if( element.title == "text" ){
				crit += " AND "	 + element.name + " like '%" + element.value+"%'";
			}else if( element.title == "number" && element.value != '' ){
				crit += " AND "	 + element.name + "='" + element.value+"'";				
			}else if( element.title == "date" && element.value != '' ){
				crit += " AND " + element.name + "= str_to_date('" + element.value + "', '%d/%m/%Y')"; 
			}
		}
	}
	return crit;
	
}

/**
 * Sends an AJAX Request for a FK Search.
 * @param path URL that will receive the AJAX REQUEST (search database request)
 * @param fieldName name of the HTML Input Element
 * 					that will receive the foreign key value, when the user clicks on a table ROW.
 * @param tableName Name of the database table that is been searched.
 * @param columnName Name of the column that is Primary Key of the searched table. This is the column that
 * 					will fill the element "fieldName" when the user clicks on a ROW.
 * @param criteria The database search criteria.
 * @param divName name of the HTML DIV Element that will receive the request response, eg. the TABLE code after
 * 					the search.
 * @param tableOnly Specifies if the response should return only the table code (after the search) or
 * 					both table and the SEARCH TABLE FORM
 * 
 */ 
function sendFKSearchRequest(path,fieldName,tableName,columnName,criteria,order,orderType,pageNumber,divName,tableOnly){	
	var searchDiv = ge(divName);
	if( tableOnly ){ 
		startLoadingImage("fk_"+tableName);
	}else{		
		changeVisibility(divName);
		if( searchDiv.style.display == 'none' ){
			return;
		}
	}
	
	var url = path;	
	var params = "action=FK_SEARCH&field="+fieldName+"&table="+tableName+"&column="+columnName+"&path="+path + "&criteria=" + criteria+"&tableOnly="+tableOnly+"&readonly=true"+"&order="+order+"&orderType="+orderType+"&pageNumber="+pageNumber;	
	runningRequest = true;	
	request = $.ajax({
		type: "GET",
		url: url,
		data: params,
		success: function(msg){ 	
			searchDiv.innerHTML = msg;
			runningRequest = false;	
			extraiScript(msg);		
		}
	});
}

// copyright 1999 Idocs, Inc. http://www.idocs.com
// Distribute this script freely but keep this notice in place
function numbersonly(myfield, e, dec)
{
var key;
var keychar;

if (window.event)
   key = window.event.keyCode;
else if (e)
   key = e.which;
else
   return true;
keychar = String.fromCharCode(key);

// control keys
if ((key==null) || (key==0) || (key==8) || 
    (key==9) || (key==13) || (key==27) )
   return true;

// numbers
else if ((("-0123456789").indexOf(keychar) > -1))
   return true;

// decimal point jump
else if (dec && (keychar == "."))
   {
   myfield.form.elements[dec].focus();
   return false;
   }
else
   return false;
}


function extraiScript(texto){
//Maravilhosa função feita pelo SkyWalker.TO do imasters/forum
//http://forum.imasters.com.br/index.php?showtopic=165277
        // inicializa o inicio ><
        var ini = 0;
        // loop enquanto achar um script
        while (ini!=-1){
                // procura uma tag de script
                ini = texto.indexOf('<script', ini);
                // se encontrar
                if (ini >=0){
                        // define o inicio para depois do fechamento dessa tag
                        ini = texto.indexOf('>', ini) + 1;
                        // procura o final do script
                        var fim = texto.indexOf('</script>', ini);
                        // extrai apenas o script
                        codigo = texto.substring(ini,fim);
                        // executa o script
                        //eval(codigo);
                        /**********************
                        * Alterado por Micox - micoxjcg@yahoo.com.br
                        * Alterei pois com o eval não executava funções.
                        ***********************/
                        novo = document.createElement("script")
                        novo.text = codigo;
                        document.body.appendChild(novo);
                }
        }
}



