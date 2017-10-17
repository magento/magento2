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
     * @return string
     */
    public function getJsLayout(): string
    {
        $buttonText = $this->instantPurchaseConfig->getButtonText();
        $this->jsLayout['components']['instant-purchase']['config']['buttonText'] = $buttonText;

        return parent::getJsLayout();
    }
}
