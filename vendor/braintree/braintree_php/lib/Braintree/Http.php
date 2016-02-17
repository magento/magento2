<?php
/**
 * Braintree HTTP Client
 * processes Http requests using curl
 *
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_Http extends Braintree_HttpBase
{
    protected $_config;

    public function __construct($config)
    {
        $this->_config = $config;
    }

    public function delete($path)
    {
        $response = $this->_doRequest('DELETE', $path);
        if($response['status'] === 200) {
            return true;
        } else {
            Braintree_Util::throwStatusCodeException($response['status']);
        }
    }

    public function get($path)
    {
        $response = $this->_doRequest('GET', $path);
        if($response['status'] === 200) {
            return Braintree_Xml::buildArrayFromXml($response['body']);
        } else {
            Braintree_Util::throwStatusCodeException($response['status']);
        }
    }

    public function post($path, $params = null)
    {
        $response = $this->_doRequest('POST', $path, $this->_buildXml($params));
        $responseCode = $response['status'];
        if($responseCode === 200 || $responseCode === 201 || $responseCode === 422) {
            return Braintree_Xml::buildArrayFromXml($response['body']);
        } else {
            Braintree_Util::throwStatusCodeException($responseCode);
        }
    }

    public function put($path, $params = null)
    {
        $response = $this->_doRequest('PUT', $path, $this->_buildXml($params));
        $responseCode = $response['status'];
        if($responseCode === 200 || $responseCode === 201 || $responseCode === 422) {
            return Braintree_Xml::buildArrayFromXml($response['body']);
        } else {
            Braintree_Util::throwStatusCodeException($responseCode);
        }
    }

    private function _buildXml($params)
    {
        return empty($params) ? null : Braintree_Xml::buildXmlFromArray($params);
    }

    protected function _getHeaders()
    {
        return array(
            'Accept: application/xml',
            'Content-Type: application/xml',
        );
    }

    protected function _getAuthorization()
    {
        if ($this->_config->isAccessToken()) {
            return array(
                'token' => $this->_config->getAccessToken(),
            );
        } else {
            return array(
                'user' => $this->_config->getClientId(),
                'password' => $this->_config->getClientSecret(),
            );
        }
    }
}
