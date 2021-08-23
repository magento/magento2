<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleGraphQl\Plugin\Pricing\Price;

use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\CatalogRule\Pricing\Price\CatalogRulePrice;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class UpdateCatalogRulePrice
 *
 * Plugin to update catalog rule price based on customer group id
 */
class UpdateCatalogRulePrice
{
    /**
     * @var TimezoneInterface
     */
    private $dateTime;

    /**
     * @var Rule
     */
    private $ruleResource;

    /**
     * @param TimezoneInterface $dateTime
     * @param Rule $ruleResource
     */
    public function __construct(
        TimezoneInterface $dateTime,
        Rule $ruleResource
    ) {
        $this->dateTime = $dateTime;
        $this->ruleResource = $ruleResource;
    }

    /**
     * Returns catalog rule value for logged in customer group
     *
     * @param CatalogRulePrice $catalogRulePrice
     * @param float|boolean $value
     * @return float|boolean
     */
    public function afterGetValue(
        CatalogRulePrice $catalogRulePrice,
        $value
    ) {
        $product = $catalogRulePrice->getProduct();
        if ($product && $product->getCustomerGroupId()) {
            $store = $product->getStore();
            $value = $this->ruleResource->getRulePrice(
                $this->dateTime->scopeDate($store->getId()),
                $store->getWebsiteId(),
                $product->getCustomerGroupId(),
                $product->getId()
            );
            $value = $value ? (float) $value : false;
        }

        return $value;
    }
}
