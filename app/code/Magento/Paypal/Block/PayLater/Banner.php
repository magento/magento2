<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Paypal\Block\PayLater;

use Magento\Framework\View\Element\Template;
use Magento\Paypal\Model\PayLaterConfig;

/**
 * PayPal PayLater component block
 */
class Banner extends Template
{
    /**
     * @var PayLaterConfig
     */
    private $payLaterConfig;

    /**
     * @var string
     */
    private $placement = '';

    /**
     * @var string
     */
    private $position = '';

    /**
     * @param Template\Context $context
     * @param PayLaterConfig $payLaterConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PayLaterConfig $payLaterConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->placement = $data['placement'] ??  '';
        $this->position = $data['position'] ??  '';
        $this->payLaterConfig = $payLaterConfig;
    }

    /**
     * Disable block output
     *
     * @return string
     */
    protected function _toHtml(): string
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
        $attributes = array_replace($attributes, $this->getStyleAttributesConfig());
        $this->jsLayout['components']['payLater']['config']['attributes'] = $attributes;
        return parent::getJsLayout();
    }

    /**
     * Build\Get URL to PP SDK
     *
     * @return string
     */
    private function getPayPalSdkUrl(): string
    {
        return "https://www.paypal.com/sdk/js?client-id=sb&components=messages,buttons";
    }

    /**
     * Retrieve style configuration
     *
     * @return array
     */
    private function getStyleAttributesConfig(): array
    {
        return $this->payLaterConfig->getStyleConfig($this->placement);
    }

    /**
     * Check if block should be displayed
     *
     * @return bool
     */
    private function isEnabled(): bool
    {
        $enabled = $this->payLaterConfig->isEnabled($this->placement);
        return $enabled && $this->payLaterConfig->getPositionConfig($this->placement) == $this->position;
    }
}
