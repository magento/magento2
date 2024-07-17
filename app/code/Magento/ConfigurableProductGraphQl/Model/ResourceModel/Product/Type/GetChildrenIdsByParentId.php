<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\ResourceModel\Product\Type;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResourceModel;

/**
 * Get configurable product children ids by parent ids
 */
class GetChildrenIdsByParentId
{
    /**
     * Initialize
     *
     * @param ConfigurableResourceModel $resourceModel
     */
    public function __construct(
        private readonly ConfigurableResourceModel $resourceModel
    ) {
    }

    /**
     * Retrieve Required children ids by parent ids
     *
     * @param array $parentIds
     * @return array
     * @throws LocalizedException|Exception
     */
    public function execute(array $parentIds): array
    {
        $select = $this->resourceModel->getConnection()->select()->from(
            ['l' => $this->resourceModel->getMainTable()],
            ['product_id', 'parent_id']
        )->where(
            'parent_id IN (?)',
            $parentIds,
            \Zend_Db::INT_TYPE
        );

        $childrenIds = [];
        foreach ($this->resourceModel->getConnection()->fetchAll($select) as $row) {
            $childrenIds[$row['product_id']][] = $row['parent_id'];
        }

        return $childrenIds;
    }
}
