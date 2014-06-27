<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogImportExport\Model\Export\RowCustomizer;

use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\Framework\ObjectManager;

class Composite implements RowCustomizerInterface
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $customizers;

    /**
     * @param ObjectManager $objectManager
     * @param array $customizers
     */
    public function __construct(ObjectManager $objectManager, $customizers = array())
    {
        $this->objectManager = $objectManager;
        $this->customizers = $customizers;
    }

    /**
     * Prepare data for export
     *
     * @param mixed $collection
     * @param int $productIds
     * @return mixed|void
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
