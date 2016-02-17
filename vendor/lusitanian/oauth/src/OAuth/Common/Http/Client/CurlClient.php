<?php

namespace OAuth\Common\Http\Client;

use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\UriInterface;

/**
 * Client implementation for cURL
 */
class CurlClient extends AbstractClient
{
    /**
     * If true, explicitly sets cURL to use SSL version 3. Use this if cURL
     * compiles with GnuTLS SSL.
     *
     * @var bool
     */
    private $forceSSL3 = false;

    /**
     * Additional parameters (as `key => value` pairs) to be passed to `curl_setopt`
     *
     * @var array
     */
    private $parameters = array();

    /**
     * Additional `curl_setopt` parameters
     *
     * @param array $parameters
     */
    public function setCurlParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param bool $force
     *
     * @return CurlClient
     */
    public function setForceSSL3($force)
    {
        $this->forceSSL3 = $force;

        return $this;
    }

    /**
     * Any implementing HTTP providers should send a request to the provided endpoint with the parameters.
     * They should return, in string form, the response body and throw an exception on error.
     *
     * @param UriInterface $endpoint
     * @param mixed        $requestBody
     * @param array        $extraHeaders
     * @param string       $method
     *
     * @return string
     *
     * @throws TokenResponseException
     * @throws \InvalidArgumentException
     */
    public function retrieveResponse(
        UriInterface $endpoint,
        $requestBody,
        array $extraHeaders = array(),
        $method = 'POST'
    ) {
        // Normalize method name
        $method = strtoupper($method);

        $this->normalizeHeaders($extraHeaders);

        if ($method === 'GET' && !empty($requestBody)) {
            throw new \InvalidArgumentException('No body expected for "GET" request.');
        }

        if (!isset($extraHeaders['Content-Type']) && $method === 'POST' && is_array($requestBody)) {
            $extraHeaders['Content-Type'] = 'Content-Type: application/x-www-form-urlencoded';
        }

        $extraHeaders['Host']       = 'Host: '.$endpoint->getHost();
        $extraHeaders['Connection'] = 'Connection: close';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $endpoint->getAbsoluteUri());

        if ($method === 'POST' || $method === 'PUT') {
            if ($requestBody && is_array($requestBody)) {
                $requestBody = http_build_query($requestBody, '', '&');
            }

            if ($method === 'PUT') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            } else {
                curl_setopt($ch, CURLOPT_POST, true);
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        if ($this->maxRedirects > 0) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, $this->maxRedirects);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $extraHeaders);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);

        foreach ($this->parameters as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        if ($this->forceSSL3) {
            curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        }

        $response     = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (false === $response) {
            $errNo  = curl_errno($ch);
            $errStr = curl_error($ch);
            curl_close($ch);
            if (empty($errStr)) {
                throw new TokenResponseException('Failed to request resource.', $responseCode);
            }
            throw new TokenResponseException('cURL Error # '.$errNo.': '.$errStr, $responseCode);
        }

        curl_close($ch);

        return $response;
    }
}
