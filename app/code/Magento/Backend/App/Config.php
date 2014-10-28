<?php
/**
 * Default application path for backend area
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
namespace Magento\Backend\App;

/**
 * Backend config accessor
 */
class Config implements ConfigInterface
{
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
     * @return mixed
     */
    public function getValue($path)
    {
        return $this->_scopePool->getScope(\Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, null)->getValue($path);
    }

    /**
     * Set config value in the corresponding config scope
     *
     * @param string $path
     * @param mixed $value
     * @return void
     */
    public function setValue($path, $value)
    {
        $this->_scopePool->getScope(\Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, null)->setValue($path, $value);
    }

    /**
     * Retrieve config flag
     *
     * @param string $path
     * @return bool
     */
    public function isSetFlag($path)
    {
        return !!$this->_scopePool->getScope(\Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, null)->getValue($path);
    }
}
