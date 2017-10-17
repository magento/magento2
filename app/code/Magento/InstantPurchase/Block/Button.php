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
 * Class Button
 * @api
 */
class Button extends Template
{
    /**
     * @var Config
     */
    private $InstantPurchaseConfig;

    /**
     * Button constructor.
     * @param Context $context
     * @param Config $InstantPurchaseConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $InstantPurchaseConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->InstantPurchaseConfig = $InstantPurchaseConfig;
    }

    /**
     * @return string
     */
    public function getJsLayout(): string
    {
        $buttonText = $this->InstantPurchaseConfig->getButtonText();
        $this->jsLayout['components']['one-touch-order']['config']['buttonText'] = $buttonText;

        return parent::getJsLayout();
    }
}
