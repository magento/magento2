<?php

namespace Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset;

use Magento\Webapi\Model\Laminas\Soap\Server;
use SoapClient;

/**
 * Test Class
 */
class TestLocalSoapClient extends SoapClient
{
    /**
     * Server object
     *
     * @var Server
     */
    public $server;

    /**
     * Local client constructor
     *
     * @param Server $server
     * @param string $wsdl
     * @param array $options
     */
    public function __construct(Server $server, $wsdl, $options)
    {
        $this->server = $server;
        parent::__construct($wsdl, $options);
    }

    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        ob_start();
        $this->server->handle($request);
        $response = ob_get_clean();

        return $response;
    }
}
