<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Export\RowCustomizer;

use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Composite
 *
 * @api
 * @since 2.0.0
 */
class Composite implements RowCustomizerInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $customizers;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $customizers
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager, $customizers = [])
    {
        $this->objectManager = $objectManager;
        $this->customizers = $customizers;
    }

    /**
     * Prepare data for export
     *
     * @param mixed $collection
     * @param int[] $productIds
     * @return mixed|void
     * @since 2.0.0
     */
    public function prepareData($collection, $productIds)
    {
        foreach ($this->customizers as $className) {
            $this->objectManager->get($className)->prepareData($collection, $productIds);
        }
    }

    /**
     * Set headers columns
     *
     * @param array $columns
     * @return array
     * @since 2.0.0
     */
    public function addHeaderColumns($columns)
    {
        foreach ($this->customizers as $className) {
            $columns = $this->objectManager->get($className)->addHeaderColumns($columns);
        }
        return $columns;
    }

    /**
     * Add data for export
     *
     * @param array $dataRow
     * @param int $productId
     * @return array
     * @since 2.0.0
     */
    public function addData($dataRow, $productId)
    {
        foreach ($this->customizers as $className) {
            $dataRow = $this->objectManager->get($className)->addData($dataRow, $productId);
        }
        return $dataRow;
    }

    /**
     * Calculate the largest links block
     *
     * @param array $additionalRowsCount
     * @param int $productId
     * @return array|mixed
     * @since 2.0.0
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        foreach ($this->customizers as $className) {
            $additionalRowsCount = $this->objectManager->get(
                $className
            )->getAdditionalRowsCount(
                $additionalRowsCount,
                $productId
            );
        }
        return $additionalRowsCount;
    }
}
