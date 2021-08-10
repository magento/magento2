<?php
/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Model\Laminas\Soap\Client;

use Magento\Webapi\Model\Laminas\Soap\Client;
use Magento\Webapi\Model\Laminas\Soap\Server;

/**
 * Class is intended to be used as local SOAP client which works
 * with a provided Server object.
 *
 * Could be used for development or testing purposes.
 */
class Local extends Client
{
    /**
     * Server object
     * @var Server
     */
    protected $server;

    /**
     * Local client constructor
     *
     * @param Server $server
     * @param string $wsdl
     * @param array $options
     */
    public function __construct(Server $server, $wsdl, $options = null)
    {
        $this->server = $server;

        // Use Server specified SOAP version as default
        $this->setSoapVersion($server->getSoapVersion());

        parent::__construct($wsdl, $options);
    }

    // @codingStandardsIgnoreStart
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
    // @codingStandardsIgnoreEnd
}
