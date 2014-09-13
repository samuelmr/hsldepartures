<?php

 $kamo_url = 'http://omatlahdot.hkl.fi/interfaces/kamo';
 # $kamo_wsdl = "$kamo_url?wsdl"; // download this and save to ./local.wsdl
 $kamo_wsdl = "./local.wsdl";
 $stops = isset($_REQUEST['stops']) ? $_REQUEST['stops'] : array();
 $max = isset($_REQUEST['max']) ? $_REQUEST['max'] : 10;
 $time = isset($_REQUEST['date']) ? strtotime($_REQUEST['date']) : time()-120;

 $deps = array();
 $client = new SoapClient($kamo_wsdl, array('location' => $kamo_url));
 foreach ($stops as $stop) {
  if ($i > $max) {
   break;
  }
  # $kamoarr = $client->getNextDeparturesRT($stop);
  $date = date('Y-m-d\TH:i:s', $time);
  $kamoarr = $client->getNextDeparturesExtRT($stop, $date, $max);
  $deps = array_merge($deps, $kamoarr);
 }

 for ($i=0; $i<count($deps); $i++) {
  $ts = $deps[$i]->time;
  list($hour, $min, $sec) = explode(':', $ts);
  $time = mktime($hour, $min, $sec);
  if ($time < time()) {
   $time += 24 * 60 * 60;
  }
  $deps[$i]->time = $time;
  if ($deps[$i]->rtime) {
   $rts = $deps[$i]->rtime;
   list($hour, $min, $sec) = explode(':', $rts);
   $rtime = mktime($hour, $min, $sec);
   if ($rtime < time()) {
    $rtime += 24 * 60 * 60;
   }
   $deps[$i]->rtime = $rtime;
  }
 }

 $json = json_encode($deps);
 header('Access-Control-Allow-Origin: *');
 header('Content-Type: application/json');
 header('Content-Length: '.strlen($json));
 if (in_array('gzip', explode(',', $_SERVER['HTTP_ACCEPT_ENCODING']))) {
  header('Content-Encoding: gzip');
  echo gzencode($json);
 }
 else {
  echo $json;
 }

?>
