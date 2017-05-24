<?php
class infoDBfilesClient
{
    private $url;
    private $api_key;
    
    function __construct($url, $api_key)
    {
        $this->url = $url;
        $this->api_key = $api_key;
    }
    
    private function generateId() {
        $chars = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
        $id = '';
        for($c = 0; $c < 16; ++$c)
            {$id .= $chars[\mt_rand(0, \count($chars) - 1)];}
        return $id;
    }
    
    function make_query($params)
    {
        $id=$this->generateId();
	$request = array(
            'jsonrpc' => '2.0',
            'method'  => 'Files_Query',
            'params'  => json_decode($params,true),
            'id'      => $id
        );
	$headers=[
            'Content-Type: application/json',
            'Accept: application/json',
            'Client-Key: '.$this->api_key
        ];
	$ch=curl_init($this->url);
	curl_setopt_array($ch, array(
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_USERAGENT => 'JSON-RPC PHP Client',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => json_encode($request)
	));
	$jsonResponse = curl_exec($ch);
	if($jsonResponse === false) return 'Curl error: ' . curl_error($ch); 
	curl_close($ch);
        $response = json_decode($jsonResponse);

        if ($response === null) return 'JSON cannot be decoded';

        if ($response->id != $id) return 'Mismatched JSON-RPC IDs';

        if (property_exists($response, 'error'))
            return $response->error->message.' code:'. $response->error->code;
        else if (property_exists($response, 'result')){
            return $response->result;
	}
        else
            return 'Invalid JSON-RPC response code:32603';
    }
   
}