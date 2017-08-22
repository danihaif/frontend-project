<?php
error_reporting(E_ALL);  //debug
ini_set("display_errors", 1); //debug


$word1 = strval($_GET['searchWord1']);
$word2 = strval($_GET['searchWord2']);
$chart_type = strval($_GET['chart']);
$ngram = strval($_GET['ngram']);

//var_dump($word1);
//var_dump($word2);

$searchWords = array();
$sql  = array();
$number_of_search_terms = 0;
//$check = 0;
//var_dump($check);
//var_dump($ngram);

if($ngram == "true"){
	$ngram_string = $word1;
	$ngram_string = preg_replace('/\s/', '', $ngram_string); //remove whitespace
	$searchWords = explode( "," , $ngram_string ); //split into array using "," as delimiter
	//$check = 1;
}
else{
	//$check=0;
	if($word1 != "")
		array_push($searchWords,$word1);
	if($word2 != "")
		array_push($searchWords,$word2);
}
//var_dump($check);
$number_of_search_terms = count($searchWords);
//var_dump($searchWords);

$db_host        = 'localhost';
$db_user        = 'root';
$db_pass        = '1234';
$db_database    = 'mini';
$db_port        = '3306';
$db_table 		= 'mini.test';
$max_results 	= '31';

/*
 * init search terms and queries
 */
for($a = 0; $a<$number_of_search_terms; $a++)
{
	$sql[$a] = "";
	if($chart_type == "sunburstChart")
	{
		$sql[$a] = "SELECT * FROM ".$db_table." WHERE author = '".$searchWords[0]."' ORDER BY (count) DESC LIMIT ".$max_results.";" ; // search term will always be first and only
	}
	else{
		$sql[$a]="SELECT * FROM ".$db_table." WHERE word = '".$searchWords[$a]."';" ; //  sql query
	}
}

//this is checked on client side
	//if(strlen($word1) < 2 || strlen($word2) < 2 ) //search term must be longer than a single char
	//die('php strings did not parse');

	$con = mysqli_connect($db_host,$db_user,$db_pass,$db_database,$db_port);
	if (!$con) { // Check connection
		die('Could not connect: ' . mysqli_error($con));
	}

$status = mysqli_select_db($con,$db_database);
if(!$status)
	die('searcher.php: mysqli_select_db failed!');

// dynamic init
$array_of_results = array();
for($c=0; $c<$number_of_search_terms; $c++){
    $array_of_results[$c] = array(); // array of cells for column $c
}
//var_dump($number_of_search_terms);
$rowcount = 0;
for($i = 0 ; $i<$number_of_search_terms ; $i++){
	$result = mysqli_query($con,$sql[$i]);
	$data_array = mysqli_fetch_all($result , MYSQLI_ASSOC); //mysqli_fetch_array($result , MYSQLI_NUM);
	$array_of_results[$i] = $data_array;
	//echo ("$array_of_results[$i=".i."  = ".$array_of_results[$i]);
	//var_dump($array_of_results);
	$rowcount += mysqli_num_rows($result); //query error check
}

if($rowcount == 0 ){ //no results from all queries
	$array_of_results =  new stdClass(); //return empty object
}

mysqli_free_result($result);

mysqli_close($con);

header("Content-Type: application/json");
echo json_encode($array_of_results);
?>
