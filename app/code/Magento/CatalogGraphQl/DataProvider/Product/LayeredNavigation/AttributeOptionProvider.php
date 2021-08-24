<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;

/**
 * Fetch product attribute option data including attribute info
 * Return data in format:
 * [
 *  attribute_code => [
 *      attribute_code => code,
 *      attribute_label => attribute label,
 *      option_label => option label,
 *      options => [option_id => 'option label', ...],
 *  ]
 * ...
 * ]
 */
class AttributeOptionProvider
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get option data. Return list of attributes with option data
     *
     * @param array $optionIds
     * @param int|null $storeId
     * @param array $attributeCodes
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getOptions(array $optionIds, ?int $storeId, array $attributeCodes = []): array
    {
        if (!$optionIds) {
            return [];
        }

        $storeId = $storeId ?: Store::DEFAULT_STORE_ID;
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(
                ['a' => $this->resourceConnection->getTableName('eav_attribute')],
                [
                    'attribute_id' => 'a.attribute_id',
                    'attribute_code' => 'a.attribute_code',
                    'attribute_label' => 'a.frontend_label',
                    'position' => 'attribute_configuration.position'
                ]
            )
            ->joinLeft(
                ['attribute_label' => $this->resourceConnection->getTableName('eav_attribute_label')],
                "a.attribute_id = attribute_label.attribute_id AND attribute_label.store_id = {$storeId}",
                [
                    'attribute_store_label' => 'attribute_label.value',
                ]
            )
            ->joinLeft(
                ['attribute_configuration' => $this->resourceConnection->getTableName('catalog_eav_attribute')],
                'a.attribute_id = attribute_configuration.attribute_id',
                []
            )
            ->joinLeft(
                ['options' => $this->resourceConnection->getTableName('eav_attribute_option')],
                'a.attribute_id = options.attribute_id',
                []
            )
            ->joinLeft(
                ['option_value' => $this->resourceConnection->getTableName('eav_attribute_option_value')],
                'options.option_id = option_value.option_id',
                [
                    'option_id' => 'option_value.option_id',
                ]
            )->joinLeft(
                ['option_value_store' => $this->resourceConnection->getTableName('eav_attribute_option_value')],
                "options.option_id = option_value_store.option_id AND option_value_store.store_id = {$storeId}",
                [
                    'option_label' => $connection->getCheckSql(
                        'option_value_store.value_id > 0',
                        'option_value_store.value',
                        'option_value.value'
                    )
                ]
            )->where(
                'a.attribute_id = options.attribute_id AND option_value.store_id = ?',
                Store::DEFAULT_STORE_ID
            )->order(
                'options.sort_order ' . Select::SQL_ASC
            );

        $select->where('option_value.option_id IN (?)', $optionIds);

        if (!empty($attributeCodes)) {
            $select->orWhere(
                'a.attribute_code in (?) AND a.frontend_input = \'boolean\'',
                $attributeCodes
            );
        }

        return $this->formatResult($select);
    }

    /**
     * Format result
     *
     * @param Select $select
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    private function formatResult(Select $select): array
    {
        $statement = $this->resourceConnection->getConnection()->query($select);

        $result = [];
        while ($option = $statement->fetch()) {
            if (!isset($result[$option['attribute_code']])) {
                $result[$option['attribute_code']] = [
                    'attribute_id' => $option['attribute_id'],
                    'attribute_code' => $option['attribute_code'],
                    'attribute_label' => $option['attribute_store_label']
                        ? $option['attribute_store_label'] : $option['attribute_label'],
                    'position' => $option['position'],
                    'options' => [],
                ];
            }
            if (!empty($option['option_id'])) {
                $result[$option['attribute_code']]['options'][$option['option_id']] = $option['option_label'];
            }
        }

        return $result;
    }
}
