<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price\Validation;

/**
 * Validation Result is used to aggregate errors that occurred during price update.
 *
 * @api
 * @since 102.0.0
 */
class Result
{
    /**
     * @var \Magento\Catalog\Api\Data\PriceUpdateResultInterfaceFactory
     */
    private $priceUpdateResultFactory;

    /**
     * Failed items.
     *
     * @var array
     */
    private $failedItems = [];

    /**
     * @param \Magento\Catalog\Api\Data\PriceUpdateResultInterfaceFactory $priceUpdateResultFactory
     */
    public function __construct(
        \Magento\Catalog\Api\Data\PriceUpdateResultInterfaceFactory $priceUpdateResultFactory
    ) {
        $this->priceUpdateResultFactory = $priceUpdateResultFactory;
    }

    /**
     * Add failed price identified, message and message parameters, that occurred during price update.
     *
     * @param int $id Failed price identified.
     * @param string $message Failure reason message.
     * @param array $parameters (optional). Placeholder values in ['placeholder key' => 'placeholder value'] format
     * for failure reason message.
     * @return void
     * @since 102.0.0
     */
    public function addFailedItem($id, $message, array $parameters = [])
    {
        $this->failedItems[$id][] = [
            'message' => $message,
            'parameters' => $parameters
        ];
    }

    /**
     * Get ids of rows, that contained errors during price update.
     *
     * @return int[]
     * @since 102.0.0
     */
    public function getFailedRowIds()
    {
        return $this->failedItems ? array_keys($this->failedItems) : [];
    }

    /**
     * Get price update errors, that occurred during price update.
     *
     * @return \Magento\Catalog\Api\Data\PriceUpdateResultInterface[]
     * @since 102.0.0
     */
    public function getFailedItems()
    {
        $failedItems = [];

        foreach ($this->failedItems as $items) {
            foreach ($items as $failedRecord) {
                $resultItem = $this->priceUpdateResultFactory->create();
                $resultItem->setMessage($failedRecord['message']);
                $resultItem->setParameters($failedRecord['parameters']);
                $failedItems[] = $resultItem;
            }
        }

        /**
         * Clear validation messages to prevent wrong validation for subsequent price update.
         * Work around for backward compatible changes.
         */
        $this->failedItems = [];

        return $failedItems;
    }
}
