<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\PhpEnvironment;

/**
 * Library for working with client ip address
 */
class RemoteAddress
{
    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Remote address cache
     *
     * @var string
     */
    protected $remoteAddress;

    /**
     * @var array
     */
    protected $alternativeHeaders;

    /**
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     * @param array $alternativeHeaders
     */
    public function __construct(\Magento\Framework\App\RequestInterface $httpRequest, array $alternativeHeaders = [])
    {
        $this->request = $httpRequest;
        $this->alternativeHeaders = $alternativeHeaders;
    }

    /**
     * Retrieve Client Remote Address
     *
     * @param bool $ipToLong converting IP to long format
     * @return string IPv4|long
     */
    public function getRemoteAddress($ipToLong = false)
    {
        if ($this->remoteAddress === null) {
            foreach ($this->alternativeHeaders as $var) {
                if ($this->request->getServer($var, false)) {
                    $this->remoteAddress = $this->request->getServer($var);
                    break;
                }
            }

            if (!$this->remoteAddress) {
                $this->remoteAddress = $this->request->getServer('REMOTE_ADDR');
            }
        }

        if (!$this->remoteAddress) {
            return false;
        }

        if (strpos($this->remoteAddress, ',') !== false) {
            $ipList = explode(',', $this->remoteAddress);
            $this->remoteAddress = trim(reset($ipList));
        }

        return $ipToLong ? ip2long($this->remoteAddress) : $this->remoteAddress;
    }

    /**
     * Returns internet host name corresponding to remote server
     *
     * @return string|null
     */
    public function getRemoteHost()
    {
        return $this->getRemoteAddress()
            ? gethostbyaddr($this->getRemoteAddress())
            : null;
    }
}
