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
 * Class OrderedProductAvailability
 */
class OrderedProductAvailability implements OrderedProductAvailabilityInterface
{

    /**
     * @var OrderedProductAvailabilityInterface[]
     */
    private $productAvailabilityChecks;

    public function __construct(array $productAvailabilityChecks)
    {
        $this->productAvailabilityChecks = $productAvailabilityChecks;
    }

    /**
     * @inheritdoc
     */
    public function checkAvailability(Item $item)
    {
        if ($item->getParentItem()
            && isset($this->productAvailabilityChecks[$item->getParentItem()->getProductType()])
        ) {

            $checkForType = $this->productAvailabilityChecks[$item->getParentItem()->getProductType()];
            if (!$checkForType instanceof OrderedProductAvailabilityInterface) {
                throw new ConfigurationMismatchException(__('Received check doesn\'t match interface'));
            }
            return $checkForType->checkAvailability($item);
        } else {
            return true;
        }
    }
}