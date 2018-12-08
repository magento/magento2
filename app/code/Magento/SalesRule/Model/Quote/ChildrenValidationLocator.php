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
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        return true;
    }
}
