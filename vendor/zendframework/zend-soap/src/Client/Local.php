<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Soap\Client;

use Zend\Soap\Client as SOAPClient;
use Zend\Soap\Server as SOAPServer;

/**
 * Class is intended to be used as local SOAP client which works
 * with a provided Server object.
 *
 * Could be used for development or testing purposes.
 */
class Local extends SOAPClient
{
    /**
     * Server object
     * @var SOAPServer
     */
    protected $server;

    /**
     * Local client constructor
     *
     * @param SOAPServer $server
     * @param string $wsdl
     * @param array $options
     */
    public function __construct(SOAPServer $server, $wsdl, $options = null)
    {
        $this->server = $server;

        // Use Server specified SOAP version as default
        $this->setSoapVersion($server->getSoapVersion());

        parent::__construct($wsdl, $options);
    }

    /**
     * Actual "do request" method.
     *
     * @param  Common $client
     * @param  string $request
     * @param  string $location
     * @param  string $action
     * @param  int    $version
     * @param  int    $oneWay
     * @return mixed
     */
    public function _doRequest(Common $client, $request, $location, $action, $version, $oneWay = null)
    {
        // Perform request as is
        ob_start();
        $this->server->handle($request);
        $response = ob_get_clean();

        if ($response === null || $response === '') {
            $serverResponse = $this->server->getResponse();
            if ($serverResponse !== null) {
                $response = $serverResponse;
            }
        }

        return $response;
    }
}
