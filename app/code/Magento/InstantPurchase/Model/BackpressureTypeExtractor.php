<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\InstantPurchase\Model;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\Backpressure\RequestTypeExtractorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\InstantPurchase\Controller\Button\PlaceOrder;
use Magento\Quote\Model\Backpressure\OrderLimitConfigManager;

/**
 * Apply backpressure to instant purchase
 */
class BackpressureTypeExtractor implements RequestTypeExtractorInterface
{
    /**
     * @var OrderLimitConfigManager
     */
    private OrderLimitConfigManager $configManager;

    /**
     * @param OrderLimitConfigManager $configManager
     */
    public function __construct(OrderLimitConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @inheritDoc
     */
    public function extract(RequestInterface $request, ActionInterface $action): ?string
    {
        if ($action instanceof PlaceOrder && $this->configManager->isEnforcementEnabled()) {
            return OrderLimitConfigManager::REQUEST_TYPE_ID;
        }

        return null;
    }
}
