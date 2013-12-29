<?php

require_once "./lib.php";

// test redirection
$input = include_once "./input.php";
$result = array();
 foreach($input as $url => $final_url){
    $result[$url]= test_url($url, $final_url);
 }

//dump logs to verify
file_put_contents(__DIR__."/output.txt", var_export($result,true));


//-----------------------------------
function test_url($url,$to_url){
 echo PHP_EOL." === Testing [$url]";
 $res = curl_trace_redirects($url);
 $reply = "";
 $counter = 0;
 $isOk = false;
 $isOkURL="";
  foreach($res as $item) {
    $counter++;
    $reply.= " [$counter] [";
    if(isset($item['timeout'])) {
        $reply .= "Timeout reached!\n";
    } else if(isset($item['error'])) {
        $reply .= "error: ". $item['error']. "\n";
    } else {
        $reply .= $item['url'];
        if(!empty($item['redirect_url'])) {
            // redirection
            $reply .= " -> (". $item['http_code']. ")";
        }
    }
    $reply.= "]".PHP_EOL;
    // is url is matched with expecttaion
    //echo PHP_EOL."##### Checking equality ,{$item['url']}, ,{$to_url}, ####";
    if(stristr($item['url'], $to_url) !== FALSE){
      $isOk = true;
      $isOkURL=$item['url']." == ". $to_url;
      //echo PHP_EOL."##### URL MATCHED ####".PHP_EOL;
      break;
    }
 }

  $t = PHP_EOL."   Result: "
	. ($isOk===true ? " Ok" : " *** Error *** ") 
	.PHP_EOL;
  echo "    $t";

  return $reply.$t;
}
