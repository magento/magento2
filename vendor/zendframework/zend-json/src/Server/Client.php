<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Json\Server;

use Zend\Http\Client as HttpClient;
use Zend\Server\Client as ServerClient;

class Client implements ServerClient
{
    /**
     * Full address of the JSON-RPC service.
     *
     * @var string
     */
    protected $serverAddress;

    /**
     * HTTP Client to use for requests.
     *
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * Request of the last method call.
     *
     * @var Request
     */
    protected $lastRequest;

    /**
     * Response received from the last method call.
     *
     * @var Response
     */
    protected $lastResponse;

    /**
     * Request ID counter.
     *
     * @var int
     */
    protected $id = 0;

    /**
     * Create a new JSON-RPC client to a remote server.
     *
     * @param string $server Full address of the JSON-RPC service.
     * @param HttpClient $httpClient HTTP Client to use for requests.
     */
    public function __construct($server, HttpClient $httpClient = null)
    {
        $this->httpClient = $httpClient ?: new HttpClient();
        $this->serverAddress = $server;
    }

    /**
     * Sets the HTTP client object to use for connecting the JSON-RPC server.
     *
     * @param  HttpClient $httpClient New HTTP client to use.
     * @return Client Self instance.
     */
    public function setHttpClient(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * Gets the HTTP client object.
     *
     * @return HttpClient HTTP client.
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * The request of the last method call.
     *
     * @return Request Request instance.
     */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
     * The response received from the last method call.
     *
     * @return Response Response instance.
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Perform a JSON-RPC request and return a response.
     *
     * @param  Request $request Request.
     * @return Response Response.
     * @throws Exception\HttpException When HTTP communication fails.
     */
    public function doRequest($request)
    {
        $this->lastRequest = $request;

        $httpRequest = $this->httpClient->getRequest();
        if ($httpRequest->getUriString() === null) {
            $this->httpClient->setUri($this->serverAddress);
        }

        $headers = $httpRequest->getHeaders();
        $headers->addHeaders(array(
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ));

        if (!$headers->get('User-Agent')) {
            $headers->addHeaderLine('User-Agent', 'Zend_Json_Server_Client');
        }

        $this->httpClient->setRawBody($request->__toString());
        $this->httpClient->setMethod('POST');
        $httpResponse = $this->httpClient->send();

        if (!$httpResponse->isSuccess()) {
            throw new Exception\HttpException(
                $httpResponse->getReasonPhrase(),
                $httpResponse->getStatusCode()
            );
        }

        $response = new Response();

        $this->lastResponse = $response;

        // import all response data from JSON HTTP response
        $response->loadJson($httpResponse->getBody());

        return $response;
    }

    /**
     * Send a JSON-RPC request to the service (for a specific method).
     *
     * @param  string $method Name of the method we want to call.
     * @param  array $params Array of parameters for the method.
     * @return mixed Method call results.
     * @throws Exception\ErrorException When remote call fails.
     */
    public function call($method, $params = array())
    {
        $request = $this->createRequest($method, $params);

        $response = $this->doRequest($request);

        if ($response->isError()) {
            $error = $response->getError();
            throw new Exception\ErrorException(
                $error->getMessage(),
                $error->getCode()
            );
        }

        return $response->getResult();
    }

    /**
     * Create request object.
     *
     * @param  string $method Method to call.
     * @param  array $params List of arguments.
     * @return Request Created request.
     */
    protected function createRequest($method, array $params)
    {
        $request = new Request();
        $request->setMethod($method)
            ->setParams($params)
            ->setId(++$this->id);
        return $request;
    }
}
