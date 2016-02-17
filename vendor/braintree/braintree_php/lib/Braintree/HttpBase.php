<?php
/**
 * Braintree HTTP Client
 * processes Http requests using curl
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
abstract class Braintree_HttpBase
{
    protected function _doRequest($httpVerb, $path, $requestBody = null)
    {
        return $this->_doUrlRequest($httpVerb, $this->_config->baseUrl() . $path, $requestBody);
    }

    abstract protected function _getHeaders();
    abstract protected function _getAuthorization();

    public function _doUrlRequest($httpVerb, $url, $requestBody = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $httpVerb);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');

        $headers = $this->_getHeaders($curl);
        $headers[] = 'User-Agent: Braintree PHP Library ' . Braintree_Version::get();
        $headers[] = 'X-ApiVersion: ' . Braintree_Configuration::API_VERSION;

        $authorization = $this->_getAuthorization();
        if (isset($authorization['user'])) {
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, $authorization['user'] . ':' . $authorization['password']);
        } else if ($authorization['token']) {
            $headers[] = 'Authorization: Bearer ' . $authorization['token'];
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        // curl_setopt($curl, CURLOPT_VERBOSE, true);
        if ($this->_config->sslOn()) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curl, CURLOPT_CAINFO, $this->_config->caFile());
        }

        if(!empty($requestBody)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($this->_config->sslOn()) {
            if ($httpStatus == 0) {
                throw new Braintree_Exception_SSLCertificate();
            }
        }
        return array('status' => $httpStatus, 'body' => $response);
    }
}
