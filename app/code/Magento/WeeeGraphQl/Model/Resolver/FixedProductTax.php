<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\WeeeGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Weee\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Tax\Model\Config;

/**
 * Resolver for FixedProductTax object that retrieves an array of FPT attributes with prices
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
        $product = $value['model'];

        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        if ($this->weeeHelper->isEnabled($store)) {
            $attributes = $this->weeeHelper->getProductWeeeAttributesForDisplay($product);
            foreach ($attributes as $attribute) {
                $displayInclTaxes = $this->taxHelper->getPriceDisplayType($store);
                $amount = $attribute->getData('amount');
                //add display mode for WEE to not return WEE if excluded
                if ($displayInclTaxes === Config::DISPLAY_TYPE_EXCLUDING_TAX) {
                    $amount = $attribute->getData('amount_excl_tax');
                } elseif ($displayInclTaxes === Config::DISPLAY_TYPE_INCLUDING_TAX) {
                    $amount = $attribute->getData('amount_excl_tax') + $attribute->getData('tax_amount');
                }
                $fptArray[] = [
                    'amount' => [
                        'value' => $amount,
                        'currency' => $value['final_price']['currency'],
                    ],
                    'label' => $attribute->getData('name')
                ];
            }
        }

        return $fptArray;
    }
}
