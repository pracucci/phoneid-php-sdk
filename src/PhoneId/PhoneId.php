<?php

namespace PhoneId;

use PhoneId\PhoneIdClient;
use PhoneId\Exceptions\PhoneIdClientException;
use PhoneId\Exceptions\PhoneIdServerException;
use PhoneId\Exceptions\PhoneIdNetworkException;


class PhoneId
{
    private $_client;
    private $_clientId;
    private $_clientSecret;
    private $_accessToken;
    private $_options;


    /**
     * Supported options:
     * - client
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

        $this->_client       = isset($options['client']) ? $options['client'] : new PhoneIdClient();
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
            'client_id'    => $this->_clientId,
            'redirect_uri' => !empty($this->_options['redirect_uri']) ? $this->_options['redirect_uri'] : null
        );

        return $this->_client->buildUrl('/static/login.html', array_merge($defaults, $params));
    }

    /**
     * @throws PhoneIdException
     * @return array
     */
    public function getMe()
    {
        return $this->_client->request('GET', '/auth/users/me');
    }

}
