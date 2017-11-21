<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Plugin\ServiceInputProcessor;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Pricing\Price\SpecialPrice;
use Magento\Framework\Webapi\ServiceInputProcessor;

/**
 * Convert product special price for API requests.
 */
class ConvertSpecialPrice
{
    /**
     * @var array
     */
    private $mapping;

    /**
     * ConvertSpecialPrice constructor.
     *
     * @param array $mapping
     */
    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * Convert special price for api requests.
     *
     * Check product special price from request. If special price = ''(remove special price from product),
     * convert it from 0 back to '', in order to save product without special price, but not with 0.00.
     *
     * @param ServiceInputProcessor $subject
     * @param \Closure $proceed
     * @param string $serviceClassName
     * @param string $serviceMethodName
     * @param array $inputArray
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function aroundProcess(
        ServiceInputProcessor $subject,
        \Closure $proceed,
        string $serviceClassName,
        string $serviceMethodName,
        array $inputArray
    ) {
        $result = $proceed($serviceClassName, $serviceMethodName, $inputArray);
        if ($serviceClassName === ProductRepositoryInterface::class && $serviceMethodName === 'save') {
            if (isset($inputArray['product'][$this->mapping['custom_attributes']])) {
                foreach ($inputArray['product'][$this->mapping['custom_attributes']] as $attribute) {
                    if ($attribute[$this->mapping['attribute_code']] === SpecialPrice::PRICE_CODE
                        && $attribute['value'] === '') {
                        $product = array_shift($result);
                        if ($product instanceof ProductInterface) {
                            $product->setCustomAttribute(SpecialPrice::PRICE_CODE, '');
                        }
                        array_unshift($result, $product);
                    }
                }
            }
        }

        return $result;
    }
}
