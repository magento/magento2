<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\GraphQl\Model\Backpressure\RequestTypeExtractorInterface;
use Magento\Quote\Model\Backpressure\OrderLimitConfigManager;
use Magento\QuoteGraphQl\Model\Resolver\PlaceOrder;
use Magento\QuoteGraphQl\Model\Resolver\SetPaymentAndPlaceOrder;

/**
 * Identifies which quote fields need backpressure management.
 */
class BackpressureRequestTypeExtractor implements RequestTypeExtractorInterface
{
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
    public function extract(Field $field): ?string
    {
        if (($field->getResolver() === SetPaymentAndPlaceOrder::class ||
                $field->getResolver()  === $this->getResolver())
            && $this->config->isEnforcementEnabled()
        ) {
            return OrderLimitConfigManager::REQUEST_TYPE_ID;
        }

        return null;
    }

    /**
     *
     * @return string
     */
    private function getResolver(): string
    {
        $reflectionClass = new \ReflectionClass(PlaceOrder::class);

        return $reflectionClass->getNamespaceName();
    }
}
