<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Reorder;

use Magento\Sales\Model\Order\Item;
use Magento\Framework\Exception\ConfigurationMismatchException;

/**
 * Class for checking availability of different product types
 *
 * Class marked as an API because it can be used as extensibility point to check availability of
 * different product types (in order management). Can be configured through di by adding a check as a new element
 * of the array $productAvailabilityChecks(constructor argument). A product type should be a key for the new element.
 *
 * @api
 * @since 100.2.0
 */
class OrderedProductAvailabilityChecker implements OrderedProductAvailabilityCheckerInterface
{

    /**
     * @var OrderedProductAvailabilityCheckerInterface[]
     */
    private $productAvailabilityChecks;

    /**
     * @param array $productAvailabilityChecks
     */
    public function __construct(array $productAvailabilityChecks)
    {
        $this->productAvailabilityChecks = $productAvailabilityChecks;
    }

    /**
     * @inheritdoc
     * @since 100.2.0
     */
    public function isAvailable(Item $item)
    {
        if ($item->getParentItem()
            && isset($this->productAvailabilityChecks[$item->getParentItem()->getProductType()])
        ) {
            $checkForType = $this->productAvailabilityChecks[$item->getParentItem()->getProductType()];
            if (!$checkForType instanceof OrderedProductAvailabilityCheckerInterface) {
                throw new ConfigurationMismatchException(__('Received check doesn\'t match interface'));
            }
            return $checkForType->isAvailable($item);
        } else {
            return true;
        }
    }
}
