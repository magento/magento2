<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Paypal\Block\PayLater;

use Magento\Framework\View\Element\Template;
use Magento\Paypal\Model\PayLaterConfig;
use Magento\Paypal\Model\SdkUrl;

/**
 * PayPal PayLater component block
 */
class Banner extends Template
{
    /**
     * @var PayLaterConfig
     */
    private $payLaterConfig;
    private $position;
    private $placement;
    private $sdkUrl;

    /**
     * @param Template\Context $context
     * @param PayLaterConfig $payLaterConfig
     * @param SdkUrl $sdkUrl
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PayLaterConfig $payLaterConfig,
        SdkUrl $sdkUrl,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->placement = $data['placement'] ??  '';
        $this->position = $data['position'] ??  '';
        $this->payLaterConfig = $payLaterConfig;
        $this->sdkUrl = $sdkUrl;
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
        $attributes = array_replace($this->getStyleAttributesConfig(), $attributes);
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
        return $this->sdkUrl->getUrl();
    }

    /**
     * Retrieve style configuration
     *
     * @return string[]
     */
    private function getStyleAttributesConfig()
    {
        return array_replace(
            ['data-pp-style-logo-position' => 'center'],
            $this->payLaterConfig->getStyleConfig($this->placement)
        );
    }

    /**
     * Check if block should be displayed
     *
     * @return bool
     */
    private function isEnabled()
    {
        $enabled = $this->payLaterConfig->isEnabled($this->placement);
        return $enabled && $this->payLaterConfig->getPositionConfig($this->placement) == $this->position;
    }
}
