<?php
//reference php api
//https://www.leaseweb.com/labs/2015/10/creating-a-simple-rest-api-in-php/
//excute this file from terminal
//php -S localhost:8888 api.php

//url
//http://localhost/api.php/{$table}/{$id}
//connect to the sybase database and output the data in json format

////debug////
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
///////////////////////

	
ini_set('mssql.charset', 'UTF-8');

//get the GET method
$method = $_SERVER['REQUEST_METHOD'];

//get the table name from the url
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));

//get the contents in the page as the json string
$input = json_decode(file_get_contents('php://input'),true);

echo 'method '.$method.'<br>';
echo '$request '.print_r($request,true).'<br>';
echo 'input '.$input.'<br>';
// connect to the mysql database

//connect to sybase wolf
$server_conn = mssql_connect(
		'host',
		'username',
		'password');

if (!$server_conn)
	echo 'fail';
	else
	{
		//connect to the database
		$db_conn = mssql_select_db('database', $server_conn);


		if($db_conn){
			
			// retrieve the database table name and key(les_s) from the path
			$table = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
			$key = array_shift($request)+0;
			
			//since les_s is char(9) and if the key is not char 9 then add whitespace to make it char 9.
			$count =  strlen($key);
			$whitespace = 9-$count;
			$les_s =  str_repeat(' ',$whitespace).$key; 
			
	
			
			// create SQL based on HTTP method
			switch ($method) {
				case 'GET':
					//$sql = "select * from `$table`".($key?" WHERE les_s=$key":''); break;
					$sql = "select * from $table".($key? " WHERE les_s= '$les_s'":''); break;
					//case 'PUT':
					//	$sql = "update `$table` set $set where id=$key"; break;
					//case 'POST':
					//	$sql = "insert into `$table` set $set"; break;
					//case 'DELETE':
					//	$sql = "delete `$table` where id=$key"; break;
			}
			
			// excecute SQL statement
			$result = mssql_query($sql);
			
			// die if SQL statement failed
			if (!$result) {
				http_response_code(404);
				die('error: no result');
				
			}else{
			
				// print results, insert id or affected row count
				if ($method == 'GET') {
				
					//creat the json format here
					if (!$key) echo '[';
					for ($i=0;$i<mssql_num_rows($result);$i++) {
						echo ($i>0?',':'').json_encode(mssql_fetch_object($result));
					}
					if (!$key) echo ']';
					
			 	}  /* elseif ($method == 'POST') {
					echo mysqli_insert_id($link);
				} else {
					echo mysqli_affected_rows($link);
				}
				
				*/
			}//else
				
			
		}else{
				
			echo 'database connection error';
		}

	}//else



// escape the columns and values from the input object
/* if($input!=''){
	
	$columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
	$values = array_map(function ($value) use ($server_conn) {
		if ($value===null) return null;
		return mssql_real_escape_string($server_conn,(string)$value);
	},array_values($input));

	// build the SET part of the SQL command
	$set = '';
	for ($i=0;$i<count($columns);$i++) {
		$set.=($i>0?',':'').'`'.$columns[$i].'`=';
		$set.=($values[$i]===null?'NULL':'"'.$values[$i].'"');
	}
//}//if($input!=''){
*/	

	// close mysql connection
	mssql_close($server_conn);
	