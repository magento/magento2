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
     * @var \Magento\OneTouchOrdering\Helper\Data
     */
    private $oneTouchOrderingHelper;

    public function __construct(
        Template\Context $context,
        \Magento\OneTouchOrdering\Helper\Data $oneTouchOrderingHelper,
        OneTouchOrdering $oneTouchOrdering,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->oneTouchOrdering = $oneTouchOrdering;
        $this->oneTouchOrderingHelper = $oneTouchOrderingHelper;
    }

    public function isButtonEnabled()
    {
        return $this->oneTouchOrdering->isOneTouchOrderingAvailable();
    }

    public function getJsLayout()
    {
        $buttonText = $this->oneTouchOrderingHelper->getButtonText();
        $this->jsLayout['components']['one-touch-order']['config']['buttonText'] = $buttonText;

        return parent::getJsLayout();
    }
}
