<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\UnionExpression;

class AttributeWebsiteRequired
{
    /**
     * @var ResourceModel\Attribute
     */
    private ResourceModel\Attribute $attribute;

    /**
     * @param ResourceModel\Attribute $attribute
     */
    public function __construct(
        ResourceModel\Attribute $attribute
    ) {
        $this->attribute = $attribute;
    }

    /**
     * Returns the attributes value 'is_required' for all websites.
     *
     * @param array $attributeIds
     * @param array $websiteIds
     * @return array
     */
    public function get(array $attributeIds, array $websiteIds): array
    {
        $defaultScope = 0;
        $connection = $this->attribute->getConnection();
        $selects[] = $connection->select()->from(
            [$this->attribute->getTable('customer_eav_attribute_website')],
            ['attribute_id', 'website_id', 'is_required']
        )->where('attribute_id IN (?) AND is_required IS NOT NULL', $attributeIds);

        $selects[] = $connection->select()->from(
            [$this->attribute->getTable('eav_attribute')],
            ['attribute_id', 'website_id' => new \Zend_Db_Expr($defaultScope), 'is_required']
        )->where('attribute_id IN (?) AND is_required IS NOT NULL', $attributeIds);

        $unionSelect = new UnionExpression($selects, Select::SQL_UNION_ALL);
        $data = $connection->fetchAll($unionSelect);
        $isRequired = [];
        foreach ($data as $row) {
            $isRequired[$row['website_id']][$row['attribute_id']] = (bool)$row['is_required'];
        }

        $result = [];
        foreach ($attributeIds as $attributeId) {
            foreach ($websiteIds as $websiteId) {
                if (isset($isRequired[$websiteId][$attributeId])) {
                    if ($isRequired[$websiteId][$attributeId]) {
                        $result[$attributeId][] = $websiteId;
                    }
                } elseif ($isRequired[$defaultScope][$attributeId]) {
                    $result[$attributeId][] = $websiteId;
                }
            }
        }

        return $result;
    }
}
