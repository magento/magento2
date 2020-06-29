<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogCustomerGraphQl\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class TierPrice extends \Magento\Catalog\Pricing\Price\TierPrice
{
    /**
     * TierPrice constructor.
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param Session $customerSession
     * @param GroupManagementInterface $groupManagement
     * @param int $customerGroupId
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        PriceCurrencyInterface $priceCurrency,
        Session $customerSession,
        GroupManagementInterface $groupManagement,
        $customerGroupId
    ) {
        parent::__construct(
            $saleableItem,
            $quantity,
            $calculator,
            $priceCurrency,
            $customerSession,
            $groupManagement
        );

        $this->customerGroup = $customerGroupId;
    }
}
