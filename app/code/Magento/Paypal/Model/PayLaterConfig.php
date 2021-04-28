<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model;

use Magento\Checkout\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provides configuration values for PayPal PayLater Banners
 */
class PayLaterConfig
{
    const PLACEMENT_PRODUCT = 'product';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get configured styles for specified page
     *
     * @return array
     */
    public function getStyleConfig(string $placement): array
    {
        return $this->getSectionConfig($placement, 'style') ?? [];
    }

    /**
     * Get configured Banner position on specified page
     *
     * @param string $placement
     * @return mixed|string
     */
    public function getPositionConfig(string $placement)
    {
        return $this->getSectionConfig($placement, 'position') ?? '';
    }

    /**
     * Check if Banner enabled for specified page
     *
     * @param string $placement
     * @return bool
     */
    public function isEnabled(string $placement)
    {
        $isPayLaterEnabled = true; //read from config
        return $isPayLaterEnabled && $this->getSectionConfig($placement, 'display');
    }

    private function getSectionConfig($section, $key)
    {
        $configMock = [
            self::PLACEMENT_PRODUCT => [
                'display' => 1,
                'position' => 'header', // 'sidebar'
                'style' => [
                    'data-pp-style-logo-position' => 'right'
                ]
            ],
        ];
        return $configMock[$section][$key] ?? [];
    }
}
