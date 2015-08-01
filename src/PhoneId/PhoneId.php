<?php

namespace PhoneId;

use PhoneId\Exceptions\PhoneIdClientException;
use PhoneId\Exceptions\PhoneIdServerException;
use PhoneId\Exceptions\PhoneIdNetworkException;


class PhoneId
{
    private $_clientId;
    private $_clientSecret;
    private $_accessToken;
    private $_options;


    /**
     * Supported options:
     * - redirect_uri
     * - connect_timeout
     * - request_timeout
     * - dns_cache_timeout
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param array  $options
     */
    public function __construct($clientId, $clientSecret, $options = array())
    {
        if (empty($clientId)) {
            throw new PhoneIdClientException('Required clientId parameter is missing');
        }

        $this->_clientId     = $clientId;
        $this->_clientSecret = $clientSecret;
        $this->_accessToken  = null;

        // Store options, fallback to defaults
        $this->_options = array_merge(array(
            'connect_timeout'   => 5,
            'request_timeout'   => 10,
            'dns_cache_timeout' => 60
        ), $options);
    }

    /**
     * Sets the default access token to use with requests.
     *
     * @param string $token
     */
    public function setAccessToken($token)
    {
        $this->_accessToken = $token;
    }

    /**
     * Returns the default access token to use with requests.
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        return $this->_accessToken;
    }

    /**
     * Returns the URL to redirect the user to, in order to start
     * the authorization process.
     *
     * @param  array  $params
     * @return string
     */
    public function getAuthorizeUrl($params = array())
    {
        $defaults = array(
            'clientId'    => $this->_clientId,
            'redirectURL' => !empty($this->_options['redirect_uri']) ? $this->_options['redirect_uri'] : null
        );

        return $this->_buildUrl('/static/login.html', array_merge($defaults, $params));
    }

    /**
     * @throws PhoneIdException
     * @return array
     */
    public function getMe()
    {
        return $this->_request('GET', '/auth/users/me');
    }

    /**
     * @param string $path
     * @param array  $params
     * @return string
     */
    private function _buildUrl($path, $params)
    {
        $url = 'https://api.phone.id/v2' . $path;

        // Filter out empty params
        $params = array_filter($params, function($value) {
            return !empty($value);
        });

        // Add query string
        if (!empty($params)) {
            $url .= (strpos($path, '?') === false ? '?' : '&') . http_build_query($params);
        }

        return $url;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $data
     */
    private function _request($method, $url, $data = array())
    {
        // Init curl
        $handle = curl_init($url);
        if ($handle === false) {
            throw new PhoneIdClientException("Unable to init curl with url: $url");
        }

        // Prepare headers
        if ($this->_accessToken) {
            // TODO $headers['Authorization'] = 'Bearer ' . $this->_accessToken;
            $data['access_token'] = $this->_accessToken;
        }

        // Curl options
        curl_setopt_array($handle, array(
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_FOLLOWLOCATION    => true,
            CURLOPT_MAXREDIRS         => 3,
            CURLOPT_TIMEOUT           => $this->_options['request_timeout'],
            CURLOPT_CONNECTTIMEOUT    => $this->_options['connect_timeout'],
            CURLOPT_DNS_CACHE_TIMEOUT => $this->_options['dns_cache_timeout'],
            CURLOPT_HTTPHEADER        => $headers
        ));

        // Config method
        switch(strtoupper($method))
        {
            case 'POST':
                curl_setopt($handle, CURLOPT_POST, true);
                curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
                break;

            case 'GET':
                curl_setopt($handle, CURLOPT_HTTPGET, true);
                curl_setopt($handle, CURLOPT_URL, $this->_buildUrl($url, $data));
                break;

            default:
                throw new PhoneIdClientException("Unsupported method: $method");
        }

        // Exec request
        $response = curl_exec($handle);
        $error    = curl_errno($handle);
        $status   = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        if ($error) {
            throw new PhoneIdNetworkException("Unable to execute request: " . curl_error($handle), 0);
        }

        // Try to decode response
        $decoded = @json_decode($response, true);
        if (empty($decoded)) {
            throw new PhoneIdServerException("Unable to decode response: $response", $status);
        }

        // Check status code
        if ($status >= 500 && $status < 600) {
            throw new PhoneIdServerException("An error occured while executing your request (status code: $status, message: " . (!empty($decoded['message']) ? $decoded['message'] : 'N/A') . ")", $status);
        } else if ($status < 200 || $status >= 300) {
            throw new PhoneIdClientException("An error occured while executing your request (status code: $status, message: " . (!empty($decoded['message']) ? $decoded['message'] : 'N/A') . ")", $status);
        }

        return $decoded;
    }

}
