<?php
/**
 * Application configuration object. Used to access configuration when application is initialized and installed.
 *
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
namespace Magento\Framework\App;

class Config implements \Magento\Framework\App\Config\ScopeConfigInterface
{
    /**
     * Config cache tag
     */
    const CACHE_TAG = 'CONFIG';

    /**
     * @var \Magento\Framework\App\Config\ScopePool
     */
    protected $_scopePool;

    /**
     * @param \Magento\Framework\App\Config\ScopePool $scopePool
     */
    public function __construct(\Magento\Framework\App\Config\ScopePool $scopePool)
    {
        $this->_scopePool = $scopePool;
    }

    /**
     * Retrieve config value by path and scope
     *
     * @param string $path
     * @param string $scope
     * @param null|string $scopeCode
     * @return mixed
     */
    public function getValue(
        $path = null,
        $scope = \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT,
        $scopeCode = null
    ) {
        return $this->_scopePool->getScope($scope, $scopeCode)->getValue($path);
    }

    /**
     * Retrieve config flag
     *
     * @param string $path
     * @param string $scope
     * @param null|string $scopeCode
     * @return bool
     */
    public function isSetFlag($path, $scope = \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, $scopeCode = null)
    {
        return (bool) $this->getValue($path, $scope, $scopeCode);
    }
}
