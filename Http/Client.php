<?php

class Client {
    const DEFAULT_TIMEOUT = 60;

    public $curl;
	
	public $options = array();

	//TODO: error 
	public $error = false;
	public $curlErrorMessage = null;
    public $curlErrorCode = null;

	public $url;

	public $headers = array();
	
	public $requestHeaders = null;
	public $responseHeaders = null;

	public $response;
	
	public function __construct($url = null) {

	    $this->curl = curl_init();
	    $this->setDefaultUserAgent();
	    $this->setDefaultTimeout();
	    $this->setOpt(CURLOPT_RETURNTRANSFER, true);
	    $this->setURL($url);
	}
	
	//return : true/false
	public function setPort($port) {
	    return $this->setOpt(CURLOPT_PORT, $port);
	}

	//return : true/false
	public function setHeader($key, $value) {
	    $this->headers[$key] = $value;
	    $headers = array();
	    foreach($this->headers as $key => $value)
	        $headers[] = $key . ': ' . $value;
	   return $this->setOpt(CURLOPT_HTTPHEADER, $headers);
	}

	//return : response(json)
	public function get($url) {
	    if (!$url) {
		    $url = $this->url;
	    }
	       
	    $this->setURL($url);
	    $this->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
	    $this->setOpt(CURLOPT_HTTPGET, true);

        echo "<br/>get: $url<br/>";

        echo "headers: ";
        print_r($this->headers);
        echo"<br/>";

        echo "options: ";
        print_r($this->options);
        echo "<br/>";

        $response =  $this->exec();

        $this->setOpt(CURLOPT_HTTPGET, false);
        return $response;
    }

	//return : response(json)    
    public function post($url, $data = array()) {
	    if (is_array($url)) {
	        $data = $url;
            $url = $this->url;
	    }

        $this->setURL($url);
        $this->setHeader("Content-Type", "application/json");
	    $this->setOpt(CURLOPT_CUSTOMREQUEST, 'POST');
	    $this->setOpt(CURLOPT_POST, true);

	    $this->setOpt(CURLOPT_POSTFIELDS, json_encode($data));
	    echo "<br/>post():$url<br/>";
        echo "<br/>headers: ";
        print_r($this->headers);
        echo "<br/>";

        echo "options: ";
        print_r($this->options);
        echo "<br/>";

	    $response =  $this->exec();

        $this->setOpt(CURLOPT_POST, false);
        return $response;
	}

    public function post_json($json_data) {
        $this->setURL($this->url);
        $this->setHeader("Content-Type", "application/json");
        //$this->setOpt(CURLOPT_CUSTOMERQUEST, 'POST');
        $this->setOpt(CURLOPT_POST, true);
        $this->setOpt(CURLOPT_POSTFIELDS, $json_data);
        echo "<br/>post_json():$this->url<br/>";
        return $this->exec();
    }

    public function put($url, $data = array()) {
        if (is_array($url)) {
            $data = $url;
            $url = $this->url;
        }
        $this->setURL($url);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'PUT');
        $put_json_data = json_encode($data);
        $this->setHeader("Content-Type", "application/json");
        if (empty($this->options[CURLOPT_INFILE]) && empty($this->options[CURLOPT_INFILESIZE])) {
            $this->setHeader('Content-Length', strlen($put_json_data));
        }
        $this->setOpt(CURLOPT_POSTFIELDS, $put_json_data);
        return $this->exec();
    }

	//return : response(json)
	public function delete($url, $data = array()) {
	    if (is_array($url)) {
	        $data = $url;
		    $url = $this->url;
	    }
	    
	    $this->setURL($url);
	    $this->setOpt(CURLOPT_CUSTOMREQUEST, 'DELETE');
	    $this->setOpt(CURLOPT_POSTFIELDS, json_encode($data));
	    echo "<br/>delete: <br/>";
        echo "url: $url<br/>";
	    return $this->exec();
	}

	//return : response(json)	
	public function exec() {
	    $this->response = curl_exec($this->curl);
	    $this->curlErrorCode = curl_errno($this->curl);
	    $this->curlErrorMessage = curl_error($this->curl);
	    return $this->response;
	}

	public function setDefaultUserAgent() {
	    $user_agent = "User-Agent:php-curl-agent";
	    $this->setUserAgent($user_agent);
	    echo "setDefaultUserAgent\n";
	}

	public function setUserAgent($user_agent) {
	    $this->setOpt(CURLOPT_USERAGENT, $user_agent);
	}
	
	public function setDefaultTimeout() {
	    $this->setTimeout(self::DEFAULT_TIMEOUT);
	}

	public function setOpt($option, $value) {
	    $returntransfer = "CURLOPT_RETURNTRANSFER";
	    if ((strcmp($returntransfer, $option) == 0) &&
	        !($value === true)) {
	        trigger_error($option . 'is a required option.');
	    }

	    $this->options[$option] = $value;
	    return curl_setopt($this->curl, $option, $value);
	}
	
	//HACK: http_build_query do not know
	//public function buildURL($url, $data = array()) {
	//    return $url . (empty($data) ? '': '?'. http_build_query($data));
	//}

	//return : true / false
	public function setURL($url){
	    $this->url = $url;
	    return $this->setOpt(CURLOPT_URL, $this->url);
	}

	//param: $seconds : int
	//no return 
	public function setTimeout($seconds) {
	       $this->setOpt(CURLOPT_TIMEOUT, $seconds);
	}

};

    echo "helloworld client";
/*
    $post_token_arr = array(
        "auth" => array(
	    "tenantName" => "admin",
            "passwordCredentials" => array(
                "username" => "admin",
                "password" => "jcb410"
            )
        )
    );

    print_r($post_token_arr);

    $curl = new Client();
    echo "<br/>setHeader ret:" .  $curl->setHeader("Content-Type", "application/json");
    $response_json = $curl->post("http://172.16.4.31:5000/v2.0/tokens", $post_token_arr);
    $response = json_decode($response_json, true);
    echo "<br/>";

    $id = $response["access"]["token"]["id"];
    $tenant_id = $response["access"][token][tenant][id];
    echo "<br/><br/>id :" . $id;    
    echo "<br/>tenant_id :" . $tenant_id;

    $curl->setHeader("X-Auth-Token", $id);
    $response_json = $curl->get("http://172.16.4.31:9292/v2/images");
    echo "<br/><br/>";

    $url_del = "http://172.16.4.31:8774/v2/" . $tenant_id . "/servers/859e90c7-ac17-4ec2-b21e-bef8054191bc";    
    $response_json = $curl->delete($url_del);
    echo "<br/>delete response: ";

    print_r($response_json);*/
?>