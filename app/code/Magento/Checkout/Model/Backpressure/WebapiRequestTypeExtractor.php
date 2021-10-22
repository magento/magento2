<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Checkout\Model\Backpressure;

use Magento\Framework\Webapi\Backpressure\BackpressureRequestTypeExtractorInterface;

/**
 * Identifies which checkout related functionality needs backpressure management.
 */
class WebapiRequestTypeExtractor implements BackpressureRequestTypeExtractorInterface
{
    /**
     * @inheritDoc
     */
    public function extract(string $service, string $method, string $endpoint): ?string
    {
        if ($method === 'savePaymentInformationAndPlaceOrder') {
            return CheckoutLimitConfigManager::REQUEST_TYPE_ID;
        }

        return null;
    }
}
