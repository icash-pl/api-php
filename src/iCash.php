<?php

namespace iCashpl\ApiPhp;

use Exception;

class iCash
{
    protected $api = 'https://icash.pl/api/';

    protected $app_key;
    
    protected $last_uri;
    
    protected $response;
    
    /*
     * Method
     */
    const POST = 'post';
    const GET = 'get';
    
    /*
     * Uri
     */
    const STATUS = 'status';
    const SERVICES = 'services';

    /**
     * @param string $app_key
     */
    public function __construct($app_key)
    {
        $this->app_key = $app_key;
    }
    
    /**
     * Get status code
     *
     * @return mixed
     */
    public function getStatusCode($data = [])
    {
        return $this->response = $this->request(static::STATUS, static::POST, $data);
    }
    
    /**
     * Get services
     *
     * @return mixed
     */
    public function getServices()
    {
        return $this->response = $this->request(static::SERVICES, static::GET);
    }
    
    /**
     * Get service
     *
     * @return mixed
     */
    public function getService($id)
    {
        return $this->response = $this->request(static::SERVICES.'/'.$id, static::GET);
    }
    
    /**
     * @return mixed
     */
    public function response()
    {
        return $this->response;
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
     * @param string $method
     * @param array $data
     *
     * @return mixed
     * @throws Exception
     */
    public function request($uri, $method, $data = [])
    {
        $this->last_uri = $uri;
        
        $ch = $this->initCurl($method, $data);
        
        curl_setopt($ch, CURLOPT_URL, $this->api . $uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->app_key,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        
        $json = curl_exec($ch);
        $error = curl_errno($ch);
        
        curl_close($ch);
        
        if ($error > 0) {
            throw new Exception('CURL ERROR Code:'.$error);
        }
        
        return $this->decode($json);
    }
    
    /**
     * @param string $method
     * @param array $data
     *
     * @return cURL handle
     * @throws Exception
     */
    protected function initCurl($method, $data)
    {
        $ch = curl_init();
        
        switch ($method) {
            case static::POST:
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            
            case static::GET:
                break;
            
            default:
                throw new Exception('Nie obsługiwany typ: '.$method);
        }
        
        return $ch;
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
