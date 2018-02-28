<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\InstantPurchase\Model\Config;

/**
 * Configuration for JavaScript instant purchase button component.
 *
 * @api
 */
class Button extends Template
{
    /**
     * @var Config
     */
    private $instantPurchaseConfig;

    /**
     * Button constructor.
     * @param Context $context
     * @param Config $instantPurchaseConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $instantPurchaseConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->instantPurchaseConfig = $instantPurchaseConfig;
    }

    /**
     * Checks if button enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->instantPurchaseConfig->isModuleEnabled($this->getCurrentStoreId());
    }

    /**
     * @inheritdoc
     */
    public function getJsLayout(): string
    {
        $buttonText = $this->instantPurchaseConfig->getButtonText($this->getCurrentStoreId());
        $purchaseUrl = $this->getUrl('instantpurchase/button/placeOrder', ['_secure' => true]);

        // String data does not require escaping here and handled on transport level and on client side
        $this->jsLayout['components']['instant-purchase']['config']['buttonText'] = $buttonText;
        $this->jsLayout['components']['instant-purchase']['config']['purchaseUrl'] = $purchaseUrl;
        return parent::getJsLayout();
    }

    /**
     * Returns active store view identifier.
     *
     * @return int
     */
    private function getCurrentStoreId(): int
    {
        return $this->_storeManager->getStore()->getId();
    }
}
