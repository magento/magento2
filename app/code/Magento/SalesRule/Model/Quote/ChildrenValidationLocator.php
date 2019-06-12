<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
namespace Magento\SalesRule\Model\Quote;

use \Magento\Quote\Model\Quote\Item\AbstractItem as QuoteItem;

/**
 * Class ChildrenValidationLocator
 *
 * Used to determine necessity to validate rule on item's children that may depends on product type
=======

declare(strict_types=1);

namespace Magento\SalesRule\Model\Quote;

use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteItem;

/**
 * Used to determine necessity to validate rule on item's children that may depends on product type.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 */
class ChildrenValidationLocator
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
<<<<<<< HEAD
     * Checks necessity to validate rule on item's children
=======
     * Checks necessity to validate rule on item's children.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     *
     * @param QuoteItem $item
     * @return bool
     */
    public function isChildrenValidationRequired(QuoteItem $item): bool
    {
        $type = $item->getProduct()->getTypeId();
        if (isset($this->productTypeChildrenValidationMap[$type])) {
            return (bool)$this->productTypeChildrenValidationMap[$type];
        }
<<<<<<< HEAD
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return true;
    }
}
