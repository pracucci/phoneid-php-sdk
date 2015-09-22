<?php

namespace PhoneId;

use PhoneId\PhoneIdClient;
use PhoneId\Exceptions\PhoneIdClientException;


class PhoneId
{
    private $_client;
    private $_clientId;
    private $_clientSecret;
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

        // Store options, fallback to defaults
        $this->_options = array_merge(array(
            'connect_timeout'   => 5,
            'request_timeout'   => 10,
            'dns_cache_timeout' => 60,
            'access_token'      => null
        ), $options);
    }

    /**
     * Sets the default access token to use with requests.
     *
     * @param string $token
     */
    public function setAccessToken($token)
    {
        $this->_options['access_token'] = $token;
    }

    /**
     * Returns the default access token to use with requests.
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        return $this->_options['access_token'];
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
            'client_id'     => $this->_clientId,
            'redirect_uri'  => !empty($this->_options['redirect_uri']) ? $this->_options['redirect_uri'] : null,
            'response_type' => 'code'
        );

        return $this->_client->buildLoginUrl('/login', array_merge($defaults, $params));
    }

    /**
     * Exchanges an authorization code with an access token.
     *
     * @param  string $code     Authorization code to exchange
     * @param  bool   $set      True to set the access token on the local instance
     * @throws PhoneIdException
     * @return array
     */
    public function exchangeAuthorizationCode($code, $set = true)
    {
        // Exchange token
        $res = $this->_client->request('POST', '/auth/token', array(
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'client_id'     => $this->_clientId,
            'client_secret' => $this->_clientSecret), $this->_options);

        if ($set) {
            $this->setAccessToken($res['access_token']);
        }

        return $res;
    }

    /**
     * @throws PhoneIdException
     * @return array
     */
    public function getMe()
    {
        return $this->_client->request('GET', '/users/me', array(), $this->_options);
    }

}
