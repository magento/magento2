<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    public function __construct(\Magento\Framework\App\RequestInterface $httpRequest, array $alternativeHeaders = array())
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
        if (is_null($this->remoteAddress)) {
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

        return $ipToLong ? ip2long($this->remoteAddress) : $this->remoteAddress;
    }
}
