<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\PhpEnvironment;

use Magento\Framework\App\RequestInterface;

/**
 * Library for working with server ip address
 */
class ServerAddress
{
    /**
     * Request object
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param RequestInterface $httpRequest
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
