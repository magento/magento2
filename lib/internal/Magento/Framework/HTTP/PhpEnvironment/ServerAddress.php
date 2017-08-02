<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\PhpEnvironment;

use Magento\Framework\App\RequestInterface;

/**
 * Library for working with server ip address
 * @since 2.0.0
 */
class ServerAddress
{
    /**
     * Request object
     *
     * @var RequestInterface
     * @since 2.0.0
     */
    protected $request;

    /**
     * @param RequestInterface $httpRequest
     * @since 2.0.0
     */
    public function __construct(RequestInterface $httpRequest)
    {
        $this->request = $httpRequest;
    }

    /**
     * Retrieve Server IP address
     *
     * @param bool $ipToLong converting IP to long format
     * @return string IPv4|long
     * @since 2.0.0
     */
    public function getServerAddress($ipToLong = false)
    {
        $address = $this->request->getServer('SERVER_ADDR');
        if (!$address) {
            return false;
        }
        return $ipToLong ? ip2long($address) : $address;
    }
}
