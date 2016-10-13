<?php

class iCash
{
    protected $api = 'https://icash.pl/api/';

    protected $app_key;
    
    protected $last_uri;
    
    protected $response;
    
    /**
     * @param string $app_key
     */
    public function __construct($app_key)
    {
        $this->app_key = $app_key;
    }
    
    /**
     * @return mixed
     */
    public function response()
    {
        return $this->response;
    }
    
    /**
     * @return mixed
     */
    public function getStatusCode($data = array())
    {
        return $this->response = $this->request('status', $data);
    }
    
    /**
     * @return string
     */
    public function status()
    {
        if (!$this->hasResponse()) {
            $this->notResponse();
        }
        
        return $this->response->status;
    }
    
    /**
     * @return bool
     */
    public function statusOk()
    {
        return $this->status() === 'OK';
    }
    
    /**
     * @return boll
     */
    public function statusError()
    {
        return $this->status() === 'ERROR';
    }

    /**
     * @return mixed|null
     */
    public function getData()
    {
        return $this->hasData() ? $this->response->data : null;
    }

    /**
     * @return mixed|null
     */
    public function getError()
    {
        if ($this->hasError()) {
            return $this->response->error;
        }
        
        return null;
    }
    
    /**
     * @return int|null
     */
    public function getErrorCode()
    {
        if ($this->hasError()) {
            return (int)$this->response->error->code;
        }
        
        return null;
    }

    /**
     * @return bool
     */
    public function hasResponse()
    {
        return !is_null($this->response);
    }
    
    /**
     * @return bool
     */
    public function hasData()
    {
        if (!$this->hasResponse()) {
            $this->notResponse();
        }
        
        return isset($this->response->data);
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        if (!$this->hasResponse()) {
            $this->notResponse();
        }
        
        return (isset($this->response->error) && !empty($this->response->error));
    }
    
    /**
     * @return bool
     */
    public function hasErrorCode($code)
    {
        if ($this->hasError() && isset($this->response->error->code)) {
            return $this->response->error->code === (int)$code;
        }
        
        return false;
    }
    
    /**
     *
     * @param string $uri
     * @param array $data
     *
     * @return mixed
     * @throws RuntimeException
     */
    public function request($uri, $data = array())
    {
        $this->last_uri = $uri;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api . $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array_merge($data, array('app_key' => $this->app_key)));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        
        $json = curl_exec($ch);
        $error = curl_errno($ch);
        
        curl_close($ch);
        
        if ($error > 0) {
            throw new RuntimeException('CURL ERROR Code:'.$error);
        }
        
        return $this->decode($json);
    }
    
    /**
     * @param $string
     *
     * @return mixed
     * @throws Exception
     */
    protected function decode($string)
    {
        $json = json_decode($string);
        
        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                $error =  ' - Maximum stack depth exceeded';
                break;
            
            case JSON_ERROR_CTRL_CHAR:
                $error = ' - Unexpected control character found';
                break;
            
            case JSON_ERROR_SYNTAX:
                $error = ' - Syntax error, malformed JSON';
                break;
            
            case JSON_ERROR_NONE:
            default:
                return $json;
        }
        
        throw new Exception('JSON Error:'.$error);
    }
    
    /**
     * @throws Exception
     */
    protected function notResponse()
    {
        throw new Exception('Brak informacji na temat ostatniego zapytania');
    }
}
