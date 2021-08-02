<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Model\Client;

use Magento\Webapi\Model\Soap\Client as soapClient;
use Magento\Webapi\Model\Soap\Server as soapServer;

/**
 * Class Local
 */
class Local extends SOAPClient
{
    /**
     * Server object
     *
     * @var SOAPServer
     */
    protected $server;

    /**
     * Local client constructor
     *
     * @param SOAPServer $server
     * @param string $wsdl
     * @param ?array $options
     */
    public function __construct(
        SOAPServer $server,
        string $wsdl,
        ?array $options = null
    ) {
        $this->server = $server;
        $this->setSoapVersion($server->getSoapVersion());

        parent::__construct($wsdl, $options);
    }

    /**
     * @inheritDoc
     */
    public function _doRequest(
        Common $client,
        string $request,
        string $location,
        string $action,
        int $version,
        int $oneWay = null
    ): string {
        ob_start();
        $this->server->handle();
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
