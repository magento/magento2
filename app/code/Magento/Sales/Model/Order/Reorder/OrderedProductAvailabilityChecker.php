<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Reorder;

use Magento\Sales\Model\Order\Item;
use Magento\Framework\Exception\ConfigurationMismatchException;

/**
 * @api
 *
 * Class OrderedProductAvailabilityChecker
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
