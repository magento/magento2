<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Block;

use Magento\Framework\View\Element\Template;
use Magento\OneTouchOrdering\Model\OneTouchOrdering;

/**
 * Class Button
 * @package Magento\OneTouchOrdering\Block
 * @api
 */
class Button extends \Magento\Framework\View\Element\Template
{
    /**
     * @var OneTouchOrdering
     */
    private $oneTouchOrdering;
    /**
     * @var \Magento\OneTouchOrdering\Model\Config
     */
    private $oneTouchOrderingConfig;

    public function __construct(
        Template\Context $context,
        \Magento\OneTouchOrdering\Model\Config $oneTouchOrderingHelper,
        OneTouchOrdering $oneTouchOrdering,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->oneTouchOrdering = $oneTouchOrdering;
        $this->oneTouchOrderingConfig = $oneTouchOrderingHelper;
    }

    public function isButtonEnabled()
    {
        return $this->oneTouchOrdering->isOneTouchOrderingAvailable();
    }

    public function getJsLayout()
    {
        $buttonText = $this->oneTouchOrderingConfig->getButtonText();
        $this->jsLayout['components']['one-touch-order']['config']['buttonText'] = $buttonText;

        return parent::getJsLayout();
    }
}
