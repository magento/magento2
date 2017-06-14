<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Adminhtml\Product\Helper\Form;

use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Config as ConfigHelper;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Escaper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\GiftMessage\Helper\Message;
use Magento\Store\Model\ScopeInterface;

/**
 * Adminhtml additional helper block for product configuration
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @codeCoverageIgnore
 */
class Config extends ConfigHelper
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
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        ScopeConfigInterface $scopeConfig,
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
            Message::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS,
            ScopeInterface::SCOPE_STORE
        );
    }
}
