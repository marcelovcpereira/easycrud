------------------------------------------------------------------------------------------------------
----EASYCRUD Installation Guide:
------------------------------------------------------------------------------------------------------

1) Copy easycrud folder into your projects folder (wherever you want. Eg: "MyProject/plugins/")

2) Open the file "EasyCrud/Database.php" and modify database parameters




------------------------------------------------------------------------------------------------------

----EASYCRUD First Usage:
------------------------------------------------------------------------------------------------------

1) Include EasyCrud.php to your file.
Ex: <? ..... .. require_once '/plugins/EasyCrud/EasyCrud.php'; .... .... ?>

2) Create a new EasyCrud object passing table name, "pretty" table name and EASYCRUD folder location:
Ex: <? ..... .. 
	$myCrud = new EasyCrud('user_tbl', 'Users', 'plugins/EasyCrud/'); 
    .... .... ?>	 

3) Print the CRUD:
Ex: <? ..... .. 
	$myCrud->printTable(); 
    .... .... ?>

THAT'S IT!


------------------------------------------------------------------------------------------------------

----Features:
------------------------------------------------------------------------------------------------------
*Automatic checking of NOT NULL fields
*Automatic checking of Primary Keys
*Automatic checking of Foreign Keys
*Automatic checking of BLOB/CLOB fields
*Asynchronous search data via ajax
*Asynchronous insertion via ajax
*Asynchronous table sorting via ajax
*Automatic validation of numeric fields before insertion
*Easy date insertion via JQuery Datepicker
*Easy foreign keys insertions via graphical interface



------------------------------------------------------------------------------------------------------

----OBS:
------------------------------------------------------------------------------------------------------
If during insertion of a new record on a table, the system doesn't show the foreign key searcher,
be sure you are using the InnoDB Engine (MyISAM hasn't referencing rules)



------------------------------------------------------------------------------------------------------

----Extra features:
------------------------------------------------------------------------------------------------------

Nicknames:

As of the table nickname, its possible to give a nickname to table fields.
In order to do that, before printTable() method, you should call setAlias, passing the column that is being nicknamed
and the nickname value.

EX: $myCrud->setAlias('user_id','User Code');




----
Field Print pre-processing:

EasyCRUD permits you to modify the way the data is shown.
You can use the "setShowCallbackFunction" to pre-process the data output value before its shown to the user.
The first parameter is the field to be processed. Second is the function name.

EX: <? .... ..
	function myFunction($param){
		return strtoupper($param);
	}
      .... ....	
	$myCrud->setShowCallbackFunction('user_name','myFunction');
      .... ....?>	
This way, everytime EasyCrud shows the user_name field, its value will be Uppercased, even if it's not saved this way on the db.
(this function doesn't change the field, only the visual representation of it)

----


----
Fild insertion pre-processing:

EasyCRUD permits you to modify the way the data is saved on the database.
Call "setInsertCallbackFunction".
EX: <? .... ..
	function myFunction($param){
		return md5($param);
	}
	.... ....
       $myCrud->setInsertCallbackFunction('user_password',"myFunction");
    .... ....?>
This way, the user_password field will be hashed before it's saved.
----


----
Password Fields:
EasyCrud can mask password values if you set the field as a password:
EX: <? .... ..
	$myCrud->setPasswordField('user_password');
	.... ....?>

----

------------------------------------------------------------------------------------------------------

----Pagination
------------------------------------------------------------------------------------------------------
You can set the number of items per page by calling:
EX: <? .... ..
	$meuCrud->setPageSize(15);
	....?>

That will set the maximum value of 15 rows per page.

