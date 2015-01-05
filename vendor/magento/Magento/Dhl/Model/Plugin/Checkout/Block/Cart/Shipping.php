<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Dhl\Model\Plugin\Checkout\Block\Cart;

/**
 * Checkout cart shipping block plugin
 */
class Shipping
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Checkout\Block\Cart\Shipping $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetStateActive(\Magento\Checkout\Block\Cart\Shipping $subject, $result)
    {
        return (bool)$result || (bool)$this->_scopeConfig->getValue('carriers/dhl/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param \Magento\Checkout\Block\Cart\Shipping $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCityActive(\Magento\Checkout\Block\Cart\Shipping $subject, $result)
    {
        return (bool)$result || (bool)$this->_scopeConfig->getValue('carriers/dhl/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
