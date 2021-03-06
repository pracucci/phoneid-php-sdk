<?php

namespace PhoneId;

use PhoneId\Exceptions\PhoneIdClientException;
use PhoneId\Exceptions\PhoneIdServerException;
use PhoneId\Exceptions\PhoneIdNetworkException;

class PhoneIdClient
{

    /**
     * @param string $method
     * @param string $path
     * @param array  $data
     * @param array  $options
     */
    public function request($method, $path, $data = array(), $options = array())
    {
        // Init curl
        $handle = curl_init();
        if ($handle === false) {
            throw new PhoneIdClientException("Unable to init curl");
        }

        // Prepare headers
        $headers = array();

        if (!empty($options['access_token'])) {
            $headers[] = 'Authorization: Bearer ' . $options['access_token'];
        }

        // Curl options
        curl_setopt_array($handle, array(
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_FOLLOWLOCATION    => true,
            CURLOPT_MAXREDIRS         => 3,
            CURLOPT_TIMEOUT           => $options['request_timeout'],
            CURLOPT_CONNECTTIMEOUT    => $options['connect_timeout'],
            CURLOPT_DNS_CACHE_TIMEOUT => $options['dns_cache_timeout'],
            CURLOPT_HTTPHEADER        => $headers
        ));

        // Config method
        switch(strtoupper($method))
        {
            case 'POST':
                curl_setopt($handle, CURLOPT_POST, true);
                curl_setopt($handle, CURLOPT_URL, $this->buildApiUrl($path));
                curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($data));
                break;

            case 'GET':
                curl_setopt($handle, CURLOPT_HTTPGET, true);
                curl_setopt($handle, CURLOPT_URL, $this->buildApiUrl($path, $data));
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

    /**
     * @param string $path
     * @param array  $params
     * @return string
     */
    public function buildApiUrl($path, $params)
    {
        return $this->_buildUrl('api.phone.id', $path, $params);
    }

    /**
     * @param string $path
     * @param array  $params
     * @return string
     */
    public function buildLoginUrl($path, $params)
    {
        return $this->_buildUrl('login.phone.id', $path, $params);
    }

    /**
     * @param string $domain
     * @param string $path
     * @param array  $params
     * @return string
     */
    private function _buildUrl($domain, $path, $params)
    {
        $url = 'https://' . $domain . '/v2' . $path;

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

}