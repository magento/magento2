<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\OneTouchOrdering\Model\Config;

/**
 * Class Button
 * @package Magento\OneTouchOrdering\Block
 * @api
 */
class Button extends Template
{
    /**
     * @var Config
     */
    private $oneTouchOrderingConfig;

    /**
     * Button constructor.
     * @param Context $context
     * @param Config $oneTouchOrderingConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $oneTouchOrderingConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->oneTouchOrderingConfig = $oneTouchOrderingConfig;
    }

    /**
     * @return string
     */
    public function getJsLayout(): string
    {
        $buttonText = $this->oneTouchOrderingConfig->getButtonText();
        $this->jsLayout['components']['one-touch-order']['config']['buttonText'] = $buttonText;

        return parent::getJsLayout();
    }
}
