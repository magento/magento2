<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Adminhtml\Product\Helper\Form;

/**
 * Adminhtml additional helper block for product configuration
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @codeCoverageIgnore
 */
class Config extends \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Config
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * Get config value data
     *
     * @return string|null
     */
    protected function _getValueFromConfig()
    {
        return $this->_scopeConfig->getValue(
            \Magento\GiftMessage\Helper\Message::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
