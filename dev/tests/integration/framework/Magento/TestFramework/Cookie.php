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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Replacement for the native cookie model that doesn't send cookie headers in testing environment
 */
namespace Magento\TestFramework;

class Cookie extends \Magento\Core\Model\Cookie
{
    /**
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\App\RequestInterface $request = null,
        \Magento\App\ResponseInterface $response = null
    ) {
        $this->_storeManager = $storeManager;
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $request = $request ?: $objectManager->get('Magento\App\RequestInterface');
        $response = $response ?: $objectManager->get('Magento\App\ResponseInterface');
        parent::__construct($request, $response, $coreStoreConfig, $storeManager);
    }

    /**
     * Dummy function, which sets value directly to $_COOKIE super-global array instead of calling setcookie()
     *
     * @param string $name The cookie name
     * @param string $value The cookie value
     * @param int $period Lifetime period
     * @param string $path
     * @param string $domain
     * @param int|bool $secure
     * @param bool $httponly
     * @return \Magento\TestFramework\Cookie
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function set($name, $value, $period = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        $_COOKIE[$name] = $value;
        return $this;
    }

    /**
     * Dummy function, which removes value directly from $_COOKIE super-global array instead of calling setcookie()
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     * @param int|bool $secure
     * @param int|bool $httponly
     * @return \Magento\TestFramework\Cookie
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete($name, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        unset($_COOKIE[$name]);
        return $this;
    }
}
