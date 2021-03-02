<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WeeeGraphQl\Model\Resolver\Quote;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Config;
use Magento\Weee\Helper\Data;

/**
 * Resolver for FixedProductTax object that retrieves an array of FPT applied to a cart item
 */
class FixedProductTax implements ResolverInterface
{
    /**
     * @var Data
     */
    private $weeeHelper;

    /**
     * @var TaxHelper
     */
    private $taxHelper;

    /**
     * @param Data $weeeHelper
     * @param TaxHelper $taxHelper
     */
    public function __construct(Data $weeeHelper, TaxHelper $taxHelper)
    {
        $this->weeeHelper = $weeeHelper;
        $this->taxHelper = $taxHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $fptArray = [];
        $cartItem = $value['model'];

        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        if ($this->weeeHelper->isEnabled($store)) {
            $taxes = $this->weeeHelper->getApplied($cartItem);
            $displayInclTaxes = $this->taxHelper->getPriceDisplayType($store);
            foreach ($taxes as $tax) {
                $amount = $tax['amount'];
                if ($displayInclTaxes === Config::DISPLAY_TYPE_INCLUDING_TAX) {
                    $amount = $tax['amount_incl_tax'];
                }
                $fptArray[] = [
                    'amount' => [
                        'value' => $amount,
                        'currency' => $value['price']['currency'],
                    ],
                    'label' => $tax['title']
                ];
            }
        }

        return $fptArray;
    }
}
