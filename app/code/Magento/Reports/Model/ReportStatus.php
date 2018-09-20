<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;

/**
 * Is report for specified event type is enabled in system configuration
 */
class ReportStatus
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is report for specified event type is enabled in system configuration
     *
     * @param string $reportEventType
     * @return bool
     * @throws InputException
     */
    public function isReportEnabled(string $reportEventType): bool
    {
        return (bool)$this->scopeConfig->getValue('reports/options/enabled')
            && (bool)$this->scopeConfig->getValue($this->getConfigPathByEventType($reportEventType));
    }

    /**
     * @param string $reportEventType
     * @return string
     * @throws InputException
     */
    private function getConfigPathByEventType(string $reportEventType): string
    {
        $typeToPathMap = [
            Event::EVENT_PRODUCT_VIEW => 'reports/options/product_view_enabled',
            Event::EVENT_PRODUCT_SEND => 'reports/options/product_send_enabled',
            Event::EVENT_PRODUCT_COMPARE => 'reports/options/product_compare_enabled',
            Event::EVENT_PRODUCT_TO_CART => 'reports/options/product_to_cart_enabled',
            Event::EVENT_PRODUCT_TO_WISHLIST => 'reports/options/product_to_wishlist_enabled',
            Event::EVENT_WISHLIST_SHARE => 'reports/options/wishlist_share_enabled',
        ];

        if (!isset($typeToPathMap[$reportEventType])) {
            throw new InputException(
                __('System configuration is not found for report event type "%1"', $reportEventType)
            );
        }

        return $typeToPathMap[$reportEventType];
    }
}
