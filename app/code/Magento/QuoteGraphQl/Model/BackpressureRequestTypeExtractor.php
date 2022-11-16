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
 * Identifies which quote fields need backpressure management
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
        $fieldResolver = $this->resolver($field->getResolver());
        $placeOrderName = $this->resolver(PlaceOrder::class);
        $setPaymentAndPlaceOrder = $this->resolver(SetPaymentAndPlaceOrder::class);

        if (($field->getResolver() === $setPaymentAndPlaceOrder || $placeOrderName  ===  $fieldResolver)
            && $this->config->isEnforcementEnabled()
        ) {
            return OrderLimitConfigManager::REQUEST_TYPE_ID;
        }

        return null;
    }

    /**
     * Resolver to get exact class name
     *
     * @param string $class
     * @return string
     */
    private function resolver(string $class): string
    {
        return trim($class, '\\');
    }
}
