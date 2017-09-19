<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\OneTouchOrdering\Model\Config;
use Magento\OneTouchOrdering\Model\OneTouchOrdering;

/**
 * Class Button
 * @package Magento\OneTouchOrdering\Block
 * @api
 */
class Button extends Template
{
    /**
     * @var OneTouchOrdering
     */
    private $oneTouchOrdering;
    /**
     * @var Config
     */
    private $oneTouchOrderingConfig;

    /**
     * Button constructor.
     * @param Context $context
     * @param Config $oneTouchOrderingConfig
     * @param OneTouchOrdering $oneTouchOrdering
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $oneTouchOrderingConfig,
        OneTouchOrdering $oneTouchOrdering,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->oneTouchOrdering = $oneTouchOrdering;
        $this->oneTouchOrderingConfig = $oneTouchOrderingConfig;
    }

    /**
     * @return bool
     */
    public function isButtonEnabled()
    {
        return $this->oneTouchOrdering->isOneTouchOrderingAvailable();
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        $buttonText = $this->oneTouchOrderingConfig->getButtonText();
        $this->jsLayout['components']['one-touch-order']['config']['buttonText'] = $buttonText;

        return parent::getJsLayout();
    }
}
