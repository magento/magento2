<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Soap\Client;

use Zend\Http\Client\Adapter\Curl as CurlClient;
use Zend\Http\Response as HttpResponse;
use Zend\Soap\Client as SOAPClient;
use Zend\Soap\Client\Common as CommonClient;
use Zend\Soap\Exception;
use Zend\Uri\Http as HttpUri;

/**
 * .NET SOAP client
 *
 * Class is intended to be used with .NET Web Services.
 */
class DotNet extends SOAPClient
{
    /**
     * Curl HTTP client adapter.
     * @var CurlClient
     */
    protected $curlClient = null;

    /**
     * The last request headers.
     * @var string
     */
    protected $lastRequestHeaders = '';

    /**
     * The last response headers.
     * @var string
     */
    protected $lastResponseHeaders = '';

    /**
     * SOAP client options.
     * @var array
     */
    protected $options = array();

    /**
     * Should NTLM authentication be used?
     * @var boolean
     */
    protected $useNtlm = false;

    /**
     * Constructor
     *
     * @param string $wsdl
     * @param array $options
     */
    public function __construct($wsdl = null, $options = null)
    {
        // Use SOAP 1.1 as default
        $this->setSoapVersion(SOAP_1_1);

        parent::__construct($wsdl, $options);
    }

    /**
     * Do request proxy method.
     *
     * @param  CommonClient $client   Actual SOAP client.
     * @param  string       $request  The request body.
     * @param  string       $location The SOAP URI.
     * @param  string       $action   The SOAP action to call.
     * @param  int          $version  The SOAP version to use.
     * @param  int          $oneWay  (Optional) The number 1 if a response is not expected.
     * @return string The XML SOAP response.
     */
    public function _doRequest(CommonClient $client, $request, $location, $action, $version, $oneWay = null)
    {
        if (!$this->useNtlm) {
            return parent::_doRequest($client, $request, $location, $action, $version, $oneWay);
        }

        $curlClient = $this->getCurlClient();

        // @todo persistent connection ?
        $headers    = array(
            'Content-Type' => 'text/xml; charset=utf-8',
            'Method'       => 'POST',
            'SOAPAction'   => '"' . $action . '"',
            'User-Agent'   => 'PHP-SOAP-CURL',
        );
        $uri = new HttpUri($location);

        // @todo use parent set* options for ssl certificate authorization
        $curlClient->setCurlOption(CURLOPT_HTTPAUTH, CURLAUTH_NTLM)
                   ->setCurlOption(CURLOPT_SSL_VERIFYHOST, false)
                   ->setCurlOption(CURLOPT_SSL_VERIFYPEER, false)
                   ->setCurlOption(CURLOPT_USERPWD, $this->options['login'] . ':' . $this->options['password']);

        // Perform the cURL request and get the response
        $curlClient->connect($uri->getHost(), $uri->getPort());
        $curlClient->write('POST', $uri, 1.1, $headers, $request);
        $response = HttpResponse::fromString($curlClient->read());

        // @todo persistent connection ?
        $curlClient->close();

        // Save headers
        $this->lastRequestHeaders  = $this->flattenHeaders($headers);
        $this->lastResponseHeaders = $response->getHeaders()->toString();

        // Return only the XML body
        return $response->getBody();
    }

    /**
     * Returns the cURL client that is being used.
     *
     * @return CurlClient
     */
    public function getCurlClient()
    {
        if ($this->curlClient === null) {
            $this->curlClient = new CurlClient();
        }
        return $this->curlClient;
    }

    /**
     * Retrieve request headers.
     *
     * @return string Request headers.
     */
    public function getLastRequestHeaders()
    {
        return $this->lastRequestHeaders;
    }

    /**
     * Retrieve response headers (as string)
     *
     * @return string Response headers.
     */
    public function getLastResponseHeaders()
    {
        return $this->lastResponseHeaders;
    }

    /**
     * Sets the cURL client to use.
     *
     * @param  CurlClient $curlClient The cURL client.
     * @return self
     */
    public function setCurlClient(CurlClient $curlClient)
    {
        $this->curlClient = $curlClient;
        return $this;
    }

    /**
     * Sets options.
     *
     * Allows setting options as an associative array of option => value pairs.
     *
     * @param  array|\Traversable $options Options.
     * @throws \InvalidArgumentException If an unsupported option is passed.
     * @return self
     */
    public function setOptions($options)
    {
        if (isset($options['authentication']) && $options['authentication'] === 'ntlm') {
            $this->useNtlm = true;
            unset($options['authentication']);
        }

        $this->options = $options;
        return parent::setOptions($options);
    }

    /**
     * Perform arguments pre-processing
     *
     * My be overridden in descendant classes
     *
     * @param  array $arguments
     * @return array
     * @throws Exception\RuntimeException
     */
    protected function _preProcessArguments($arguments)
    {
        if (count($arguments) > 1  ||
            (count($arguments) == 1  &&  !is_array(reset($arguments)))
           ) {
            throw new Exception\RuntimeException(
                '.Net webservice arguments have to be grouped into array: array("a" => $a, "b" => $b, ...).'
            );
        }

        // Do nothing
        return $arguments;
    }

    /**
     * Perform result pre-processing
     *
     * My be overridden in descendant classes
     *
     * @param  object $result
     * @return mixed
     */
    protected function _preProcessResult($result)
    {
        $resultProperty = $this->getLastMethod() . 'Result';
        if (property_exists($result, $resultProperty)) {
            return $result->$resultProperty;
        }
        return $result;
    }

    /**
     * Flattens an HTTP headers array into a string.
     *
     * @param  array $headers The headers to flatten.
     * @return string The headers string.
     */
    protected function flattenHeaders(array $headers)
    {
        $result = '';

        foreach ($headers as $name => $value) {
            $result .= $name . ': ' . $value . "\r\n";
        }

        return $result;
    }
}
