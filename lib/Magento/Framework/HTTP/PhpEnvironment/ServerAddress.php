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
