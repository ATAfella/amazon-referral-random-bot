<?php
// recupero il contenuto inviato da Telegram
$content = file_get_contents("php://input");
// converto il contenuto da JSON ad array PHP
$update = json_decode($content, true);
// se la richiesta è null interrompo lo script
if(!$update)
{
  exit;
}
// assegno alle seguenti variabili il contenuto ricevuto da Telegram
$message = isset($update['message']) ? $update['message'] : "";
$messageId = isset($message['message_id']) ? $message['message_id'] : "";
$chatId = isset($message['chat']['id']) ? $message['chat']['id'] : "";
$firstname = isset($message['chat']['first_name']) ? $message['chat']['first_name'] : "";
$lastname = isset($message['chat']['last_name']) ? $message['chat']['last_name'] : "";
$username = isset($message['chat']['username']) ? $message['chat']['username'] : "";
$date = isset($message['date']) ? $message['date'] : "";
$text = isset($message['text']) ? $message['text'] : "";
// pulisco il messaggio ricevuto togliendo eventuali spazi prima e dopo il testo
$text = trim($text);
//$text = strtolower($text);
$array1 = array();
		
// gestisco la richiesta
$response = "";
if(isset($message['text']))

{
  //NUOVO PARSER:
  $text_url_array = parse_text($text);
	
  $text_clean = clean_for_URL($text);
  $array1 = explode('.', $text_clean);
  $dominio = $array1[1];
  if(strpos($text, "/start") === 0 )
  {
	$response = "Ciao $firstname! \nMandami un link Amazon o condividilo direttamente con me da altre app! \nTi rispondero' con il link affiliato del mio padrone!";
  }
  elseif(strcmp($dominio,"amazon") === 0)
  {
	//$response = "Good! This is an ".$dominio." link!!";
	/*$url_to_parse = $text_clean;
	$url_affiliate = set_referral_URL($url_to_parse);
	$faccinasym = json_decode('"\uD83D\uDE0A"');
	$linksym =  json_decode('"\uD83D\uDD17"');
	$pollicesym =  json_decode('"\uD83D\uDC4D"');
	$worldsym = json_decode('"\uD83C\uDF0F"');
	$response = "Ecco fatto! Di seguito il link per l'aquisto! $faccinasym \n$worldsym  $url_affiliate";*/
	  
	//new parser:
	$url_to_parse = $text_url_array[1];
	$url_affiliate = set_referral_URL($url_to_parse);
	$faccinasym = json_decode('"\uD83D\uDE0A"');
	$linksym =  json_decode('"\uD83D\uDD17"');
	$pollicesym =  json_decode('"\uD83D\uDC4D"');
	$worldsym = json_decode('"\uD83C\uDF0F"');
	$response = "Ecco fatto!$faccinasym \n$text_url_array[0]\n$worldsym  $url_affiliate";
	
  }
  elseif(strcmp($array1[0],"www") === 0)
  {
	//$response = "Wrong! This is not an Amazon link, retry!";
  }
  else
  {
	//$response = "This doesn't work, send me an Amazon link";
  }
}
/*
*
* prende un link amazon, estrapola l'ASIN e ricrea un link allo stesso prodotto con il referral 
*/
function set_referral_URL($url){
	$referral = array("miketama-21","s1m0nex27-21","antonio99-21","antcaiazza-21");
	$random = mt_rand(0,3);
	
	$url_edited = "";
	$parsed_url_array = parse_url($url);
	
	$seller = strstr($parsed_url_array['query'], 'm=');
	
	$parsed = extract_unit($fullstring, 'm=', '&');
	$seller = "&".$seller;
	$url_edited = "https://www.amazon.it".$parsed_url_array['path']."?tag=".$referral[$random].$seller;
	return $url_edited;
}

function clean_for_URL($string){
	$cleaned_string = explode(' ',strstr($string,'https://'))[0];
	if(strcmp($cleaned_string,"false") == "0"){ $cleaned_string = explode(' ',strstr($string,'http://'))[0]; }
	return $cleaned_string;
}
//nuovo parser
function parse_text($string){
	preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $string, $match);
	$text_parsed_URL = $match[0][0];
	$arr = explode("http://", $string);
	$text_parsed_TEXT = $arr[0];
	$text_parsed = array($text_parsed_TEXT, $text_parsed_URL);
	return $text_parsed;
}
function get_string_between($string, $start, $end){
	$string = ' ' . $string;
	$ini = strpos($string, $start);
	if ($ini == 0) return '';
	$ini += strlen($start);
	$len = strpos($string, $end, $ini) - $ini;
	return substr($string, $ini, $len);
}
 
function extract_unit($string, $start, $end){
	$pos = stripos($string, $start);
	$str = substr($string, $pos);
	$str_two = substr($str, strlen($start));
	$second_pos = stripos($str_two, $end);
	$str_three = substr($str_two, 0, $second_pos);
	$unit = trim($str_three); // remove whitespaces
	return $unit;
}
header("Content-Type: application/json");
$parameters = array('chat_id' => $chatId, "text" => $response);
$parameters["method"] = "sendMessage";
echo json_encode($parameters);
