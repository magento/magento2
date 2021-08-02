<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Model\Client;

use SoapClient;
use SoapFault;

/**
 * Class Common
 */
class Common extends SoapClient
{
    /**
     * doRequest() pre-processing method.
     *
     * @var callable
     */
    protected $doRequestCallback;

    /**
     * Common Soap Client constructor.
     *
     * @param callable $doRequestCallback
     * @param string $wsdl
     * @param array $options
     * @throws SoapFault
     */
    public function __construct(callable $doRequestCallback, string $wsdl, array $options)
    {
        $this->doRequestCallback = $doRequestCallback;

        parent::__construct($wsdl, $options);
    }

    /**
     * Performs SOAP request over HTTP.
     * Overridden to implement different transport layers, perform additional
     * XML processing or other purpose.
     *
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param int $oneWay
     *
     * @return false|mixed|string
     */
    public function __doRequest(
        $request,
        $location,
        $action,
        $version,
        $oneWay = 0
    ) {
        if ($oneWay === null) {
            return call_user_func($this->doRequestCallback, $this, ltrim($request), $location, $action, $version);
        }

        return call_user_func($this->doRequestCallback, $this, ltrim($request), $location, $action, $version, $oneWay);
    }
}
