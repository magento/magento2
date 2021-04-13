<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Paypal\Block\Bml;

use Magento\Framework\View\Element\Template;
use Magento\Paypal\Model\Config;

/**
 * PayPal PayLater component block
 */
class Paylater extends Template
{
    /**
     * @var \Magento\Paypal\Model\Config
     */
    protected $_paypalConfig;

    /**
     * @param Template\Context $context
     * @param Config $paypalConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $paypalConfig,
        array $data = []
    )
    {
        $this->_paypalConfig = $paypalConfig;
        parent::__construct($context, $data);
    }

    /**
     * Disable block output
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->isEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * @inheritdoc
     */
    public function getJsLayout()
    {
        $this->jsLayout['components']['payLater']['config']['sdkUrl'] = $this->getPayPalSdkUrl();
        $attributes = $this->jsLayout['components']['payLater']['config']['attributes'] ?? [];
        $attributes = array_replace($attributes, $this->getConfig());
        $this->jsLayout['components']['payLater']['config']['attributes'] = $attributes;
        return parent::getJsLayout();
    }

    /**
     * Build\Get URL to PP SDK
     *
     * @return string
     */
    private function getPayPalSdkUrl()
    {
        return "https://www.paypal.com/sdk/js?client-id=sb&components=messages,buttons";
    }

    /**
     * Retrieve style configuration
     *
     * @return string[]
     */
    private function getConfig()
    {
        return [];
    }

    /**
     * Check if block should be displayed
     *
     * @return bool
     */
    private function isEnabled()
    {
        return true;
    }
}
