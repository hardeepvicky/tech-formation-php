<?php

class PayPal
{
    /**
     * API Version
     */
    const VERSION = 90;

    /**
     * List of valid API environments
     * @var array
     */
    private $allowedEnvs = array(
        'beta-sandbox',
        'live',
        'sandbox'
    );

    /**
     * Config storage from constructor
     * @var array
     */
    private $config = array();

    /**
     * URL storage based on environment
     * @var string
     */
    private $url;

    /**
     * Build PayPal API request
     * 
     * @param string $username
     * @param string $password
     * @param string $signature
     * @param string $environment
     */
    public function __construct($environment = 'live')
    {
        if (!in_array($environment, $this->allowedEnvs)) {
            throw new Exception('Specified environment is not allowed.');
        }
        
        $this->config = array(
            'username'    => PAYPAL_API_USERNAME,
            'password'    => PAYPAL_API_PASSWORD,
            'signature'   => PAYPAL_API_SIGNATURE,
            "curreny"     => PAYPAL_CURRENCY,
            'environment' => $environment
        );
    }
    
    public function GetBalance()
    {
        return $this->call("GetBalance");
    }
    
    public function Refund($params = array())
    {
        return $this->call("MassPay", $params);
    }

    /**
     * Make a request to the PayPal API
     * 
     * @param  string $method API method (e.g. GetBalance)
     * @param  array  $params Additional fields to send in the request (e.g. array('RETURNALLCURRENCIES' => 1))
     * @return array
     */
    private function call($method, array $params = array())
    {
        $fields = array_merge(
            array(
                'USER'      => $this->config['username'],
                'PWD'       => $this->config['password'],
                'SIGNATURE' => $this->config['signature'],
                'METHOD'    => $method,
                'VERSION'   => self::VERSION,                
                'CURRENCYCODE' => $this->config['curreny']
            ),
            $params
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getUrl());                
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        if (!$response) {
            throw new Exception('Failed to contact PayPal API: ' . curl_error($ch) . ' (Error No. ' . curl_errno($ch) . ')');
        }
        curl_close($ch);
        parse_str($response, $result);
        return $this->decodeFields($result);
    }

    /**
     * Prepare fields for API
     * 
     * @param  array  $fields
     * @return array
     */
    private function encodeFields(array $fields)
    {
        return array_map('urlencode', $fields);
    }

    /**
     * Make response readable
     * 
     * @param  array  $fields
     * @return array
     */
    private function decodeFields(array $fields)
    {
        return array_map('urldecode', $fields);
    }

    /**
     * Get API url based on environment
     * 
     * @return string
     */
    private function getUrl()
    {
        if (is_null($this->url)) {
            switch ($this->config['environment']) {
                case 'sandbox':
                case 'beta-sandbox':
                    $this->url = "https://api-3t." . $this->config['environment'] . ".paypal.com/nvp";
                    break;
                default:
                    $this->url = 'https://api-3t.paypal.com/nvp';
            }
        }
        return $this->url;
    }
}