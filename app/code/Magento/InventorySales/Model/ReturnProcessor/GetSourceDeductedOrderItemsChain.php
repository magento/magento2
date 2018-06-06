<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ReturnProcessor;

use Magento\InventorySales\Model\ReturnProcessor\GetSourceDeductedOrderItemsInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Framework\Exception\InputException;
use Magento\InventorySales\Model\ReturnProcessor\Result\SourceDeductedOrderItemsResultInterfaceFactory;

class GetSourceDeductedOrderItemsChain implements GetSourceDeductedOrderItemsInterface
{
    /**
     * @var array
     */
    private $sourceDeductedItemsSelector;

    /**
     * @var SourceDeductedOrderItemsResultInterfaceFactory
     */
    private $sourceDeductedOrderItemsResultFactory;

    /**
     * @param SourceDeductedOrderItemsResultInterfaceFactory $sourceDeductedOrderItemsResultFactory
     * @param array $sourceDeductedItemsSelector
     * @throws InputException
     */
    public function __construct(
        SourceDeductedOrderItemsResultInterfaceFactory $sourceDeductedOrderItemsResultFactory,
        array $sourceDeductedItemsSelector = []
    ) {
        foreach ($sourceDeductedItemsSelector as $selector) {
            if (!$selector instanceof GetSourceDeductedOrderItemsInterface) {
                throw new InputException(
                    __('%1 doesn\'t implement GetSourceDeductedOrderItemsInterface', get_class($selector))
                );
            }
        }
        $this->sourceDeductedItemsSelector = $sourceDeductedItemsSelector;
        $this->sourceDeductedOrderItemsResultFactory = $sourceDeductedOrderItemsResultFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(OrderModel $order, array $returnToStockItems): array
    {
        $sourceDeductedItems = [];
        foreach ($this->sourceDeductedItemsSelector as $selector) {
            $resultItems = $selector->execute($order, $returnToStockItems);
            if (!empty($resultItems)) {
                $sourceDeductedItems[] = $resultItems;
            }
        }

        return $this->groupBySourceCode($sourceDeductedItems);
    }

    /**
     * @param $sourceDeductedItems
     * @return array
     */
    private function groupBySourceCode($sourceDeductedItems): array
    {
        $groupedItems = $result = [];
        foreach ($sourceDeductedItems as $resultItems) {
            foreach ($resultItems as $resultItem) {
                if (!empty($groupedItems[$resultItem->getSourceCode()])) {
                    $resultArray = array_merge($groupedItems[$resultItem->getSourceCode()], $resultItem->getItems());
                    $groupedItems[$resultItem->getSourceCode()] = $resultArray;
                } else {
                    $groupedItems[$resultItem->getSourceCode()] = $resultItem->getItems();
                }
            }
        }

        foreach ($groupedItems as $sourceCode => $items) {
            $result[] = $this->sourceDeductedOrderItemsResultFactory->create([
                'sourceCode' => $sourceCode,
                'items' => $items
            ]);
        }

        return $result;
    }
}
