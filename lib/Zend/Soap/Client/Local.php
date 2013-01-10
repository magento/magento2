<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Soap
 */

namespace Zend\Soap\Client;

use Zend\Soap\Client as SOAPClient;
use Zend\Soap\Server as SOAPServer;

/**
 * \Zend\Soap\Client\Local
 *
 * Class is intended to be used as local SOAP client which works
 * with a provided Server object.
 *
 * Could be used for development or testing purposes.
 *
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage Client
 */
class Local extends SOAPClient
{
    /**
     * Server object
     *
     * @var \Zend\Soap\Server
     */
    protected $server;

    /**
     * Local client constructor
     *
     * @param \Zend\Soap\Server $server
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
     * @internal
     * @param \Zend\Soap\Client\Common $client
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int    $version
     * @param int    $one_way
     * @return mixed
     */
    public function _doRequest(Common $client, $request, $location, $action, $version, $one_way = null)
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
