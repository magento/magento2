<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model;

use \Magento\Quote\Model\Quote\Item\AbstractItem as QuoteItem;

/**
 * Class QuoteItemChildrenValidationLocator
 *
 * Used to determine necessity to validate rule on item's children that may depends on product type
 */
class QuoteItemChildrenValidationLocator
{
    /**
     * @var array
     */
    private $productTypeChildrenValidationMap;

    /**
     * @param array $productTypeChildrenValidationMap
     * <pre>
     * [
     *      'ProductType1' => true,
     *      'ProductType2' => false
     * ]
     * </pre>
     */
    public function __construct(
        array $productTypeChildrenValidationMap = []
    ) {
        $this->productTypeChildrenValidationMap = $productTypeChildrenValidationMap;
    }

    /**
     * @param QuoteItem $item
     * @return bool
     */
    public function getNeedToValidateChildren(QuoteItem $item)
    {
        $type = $item->getProduct()->getTypeId();
        if (!empty($this->productTypeChildrenValidationMap[$type])) {
            return (bool)$this->productTypeChildrenValidationMap[$type];
        }
        return true;
    }
}
