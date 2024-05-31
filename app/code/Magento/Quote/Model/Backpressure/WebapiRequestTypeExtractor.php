<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Quote\Model\Backpressure;

use Magento\Framework\Webapi\Backpressure\BackpressureRequestTypeExtractorInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\GuestCartManagementInterface;

/**
 * Identifies which checkout related functionality needs backpressure management
 */
class WebapiRequestTypeExtractor implements BackpressureRequestTypeExtractorInterface
{
    private const METHOD = 'placeOrder';

    /**
     * @var OrderLimitConfigManager
     */
    private OrderLimitConfigManager $config;

    /**
     * @param OrderLimitConfigManager $config
     */
    public function __construct(OrderLimitConfigManager $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function extract(string $service, string $method, string $endpoint): ?string
    {
        if (in_array($service, [CartManagementInterface::class, GuestCartManagementInterface::class])
            && $method === self::METHOD
            && $this->config->isEnforcementEnabled()
        ) {
            return OrderLimitConfigManager::REQUEST_TYPE_ID;
        }

        return null;
    }
}
