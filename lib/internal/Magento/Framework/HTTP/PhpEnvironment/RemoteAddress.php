<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\PhpEnvironment;

use Magento\Framework\App\RequestInterface;

/**
 * Library for working with client ip address.
 */
class RemoteAddress
{
    /**
     * Request object.
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Remote address cache.
     *
     * @var string
     */
    protected $remoteAddress;

    /**
     * @var array
     */
    protected $alternativeHeaders;

    /**
     * @var string[]|null
     */
    private $trustedProxies;

    /**
     * @param RequestInterface $httpRequest
     * @param array $alternativeHeaders
     * @param string[]|null $trustedProxies
     */
    public function __construct(
        RequestInterface $httpRequest,
        array $alternativeHeaders = [],
        array $trustedProxies = null
    ) {
        $this->request = $httpRequest;
        $this->alternativeHeaders = $alternativeHeaders;
        $this->trustedProxies = $trustedProxies;
    }

    /**
     * Read address based on settings.
     *
     * @return string|null
     */
    private function readAddress()
    {
        $remoteAddress = null;
        foreach ($this->alternativeHeaders as $var) {
            if ($this->request->getServer($var, false)) {
                $remoteAddress = $this->request->getServer($var);
                break;
            }
        }

        if (!$remoteAddress) {
            $remoteAddress = $this->request->getServer('REMOTE_ADDR');
        }

        return $remoteAddress;
    }

    /**
     * Filter addresses by trusted proxies list.
     *
     * @param string $remoteAddress
     * @return string|null
     */
    private function filterAddress(string $remoteAddress)
    {
        if (strpos($remoteAddress, ',') !== false) {
            $ipList = explode(',', $remoteAddress);
        } else {
            $ipList = [$remoteAddress];
        }
        $ipList = array_filter(
            $ipList,
            function (string $ip) {
                return filter_var(trim($ip), FILTER_VALIDATE_IP);
            }
        );
        if ($this->trustedProxies !== null) {
            $ipList = array_filter(
                $ipList,
                function (string $ip) {
                    return !in_array(trim($ip), $this->trustedProxies, true);
                }
            );
            $remoteAddress = trim(array_pop($ipList));
        } else {
            $remoteAddress = trim(reset($ipList));
        }

        return $remoteAddress ?: null;
    }

    /**
     * Retrieve Client Remote Address.
     * If alternative headers are used and said headers allow multiple IPs
     * it is suggested that trusted proxies is also used
     * for more accurate IP recognition.
     *
     * @param bool $ipToLong converting IP to long format
     *
     * @return string IPv4|long
     */
    public function getRemoteAddress(bool $ipToLong = false)
    {
        if ($this->remoteAddress !== null) {
            return $this->remoteAddress;
        }

        $remoteAddress = $this->readAddress();
        if (!$remoteAddress) {
            $this->remoteAddress = false;

            return false;
        }
        $remoteAddress = $this->filterAddress($remoteAddress);

        if (!$remoteAddress) {
            $this->remoteAddress = false;

            return false;
        } else {
            $this->remoteAddress = $remoteAddress;

            return $ipToLong ? ip2long($this->remoteAddress) : $this->remoteAddress;
        }
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
