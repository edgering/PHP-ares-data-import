<?php
/*
 *  Ares Loader 
 *
 *  $_POST call
 *  
 *  http://wwwinfo.mfcr.cz/ares/ares_xml_standard.html.cz
 * 
 *  + JSON ouput for JS parser  
 */ 

header("Content-Type: application/json; charset=UTF-8");
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// -----------------------------------------------------------------------------

$OUTPUT = array("err" => '', "sys" => '');
$ARESCZ = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?aktivni=true&max_pocet=1&';

// -- Accept params ------------------------------------------------------------

if (isset($_POST["ic"]) && $_POST["ic"] != '')
{
  $ARESCZ .= "ico=" . preg_replace("/[^0-9]+/","",$_POST["ic"]);
}
else if (isset($_POST['firma']) && $_POST['firma'] != '')
{
  $ARESCZ .= "obchodni_firma=" . urlencode($_POST['firma']);
}
else
{
  $OUTPUT["err"] = 'Search param not found. To set > $_POST["ic"] || $_POST["firma"]';

  echo json_encode($OUTPUT);
  return;
}

// -- CONNECT to Ares server ---------------------------------------------------

$curl = curl_init(); 

curl_setopt($curl, CURLOPT_URL, $ARESCZ); 
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($curl, CURLOPT_HEADER, false); 

$data = curl_exec($curl); 

curl_close($curl); 

if (!$data)
{
  $OUTPUT["err"] = "Can't connect.";

  echo json_encode($OUTPUT);
  return;
}
else
{
  $xml = simplexml_load_string($data);
} 

// -- PARSE RESPONSE --------------------------------------------------------

if ($xml) 
{
  $nspc = $xml->getDocNamespaces();
  $data = $xml->children($nspc['are']);
  $elem = $data->children($nspc['D'])->VBAS;

  $OUTPUT["response"] = array();
        
  if (isset($elem->ICO))
  {          
    $OUTPUT["response"]['pf']    = strval($elem->PF->KPF;   
    $OUTPUT["response"]['ic'] 	 = strval($elem->ICO);
    $OUTPUT["response"]['dic']   = strval($elem->DIC);
    $OUTPUT["response"]['nazev'] = strval($elem->OF);
    
    $OUTPUT["response"]['ulice'] = $elem->AA->NU . ' ';
            
    if ($elem->AA->CD!='')
    {
      $OUTPUT["response"]['ulice']	.= strval($elem->AA->CD);
      
      if (strval($elem->AA->CO) != '') 
      { 
        $OUTPUT["response"]['ulice'] .= "/"; 
      }      
    }
    
    $OUTPUT["response"]['ulice'] .= strval($elem->AA->CO);
    
    $OUTPUT["response"]['mesto']	= strval($elem->AA->N);
    $OUTPUT["response"]['psc']	  = strval($elem->AA->PSC);    
  } 
} 
else
{
  $OUTPUT["err"] = 'No server response.';
} 

echo json_encode($OUTPUT);
