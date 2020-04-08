<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation;

use Magento\Framework\App\ResourceConnection;

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
     * @param array $attributeCodes
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getOptions(array $optionIds, array $attributeCodes = []): array
    {
        if (!$optionIds) {
            return [];
        }

        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(
                ['a' => $this->resourceConnection->getTableName('eav_attribute')],
                [
                    'attribute_id' => 'a.attribute_id',
                    'attribute_code' => 'a.attribute_code',
                    'attribute_label' => 'a.frontend_label',
                ]
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
                    'option_label' => 'option_value.value',
                    'option_id' => 'option_value.option_id',
                ]
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
     * @param \Magento\Framework\DB\Select $select
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    private function formatResult(\Magento\Framework\DB\Select $select): array
    {
        $statement = $this->resourceConnection->getConnection()->query($select);

        $result = [];
        while ($option = $statement->fetch()) {
            if (!isset($result[$option['attribute_code']])) {
                $result[$option['attribute_code']] = [
                    'attribute_id' => $option['attribute_id'],
                    'attribute_code' => $option['attribute_code'],
                    'attribute_label' => $option['attribute_label'],
                    'options' => [],
                ];
            }
            $result[$option['attribute_code']]['options'][$option['option_id']] = $option['option_label'];
        }

        return $result;
    }
}
