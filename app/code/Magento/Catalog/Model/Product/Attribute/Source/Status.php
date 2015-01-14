<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute\Source;

/**
 * Product status functionality model
 */
class Status extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource implements
    \Magento\Eav\Model\Entity\Attribute\Source\SourceInterface
{
    /**#@+
     * Product Status values
     */
    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 2;

    /**#@-*/

    /**
     * Retrieve Visible Status Ids
     *
     * @return int[]
     */
    public function getVisibleStatusIds()
    {
        return [self::STATUS_ENABLED];
    }

    /**
     * Retrieve Saleable Status Ids
     * Default Product Enable status
     *
     * @return int[]
     */
    public function getSaleableStatusIds()
    {
        return [self::STATUS_ENABLED];
    }

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * Add Value Sort To Collection Select
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @param string $dir direction
     * @return \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
     */
    public function addValueSortToCollection($collection, $dir = 'asc')
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $attributeId = $this->getAttribute()->getId();
        $attributeTable = $this->getAttribute()->getBackend()->getTable();

        if ($this->getAttribute()->isScopeGlobal()) {
            $tableName = $attributeCode . '_t';

            $collection->getSelect()->joinLeft(
                [$tableName => $attributeTable],
                "e.entity_id={$tableName}.entity_id" .
                " AND {$tableName}.attribute_id='{$attributeId}'" .
                " AND {$tableName}.store_id='0'",
                []
            );

            $valueExpr = $tableName . '.value';
        } else {
            $valueTable1 = $attributeCode . '_t1';
            $valueTable2 = $attributeCode . '_t2';

            $collection->getSelect()->joinLeft(
                [$valueTable1 => $attributeTable],
                "e.entity_id={$valueTable1}.entity_id" .
                " AND {$valueTable1}.attribute_id='{$attributeId}'" .
                " AND {$valueTable1}.store_id='0'",
                []
            )->joinLeft(
                [$valueTable2 => $attributeTable],
                "e.entity_id={$valueTable2}.entity_id" .
                " AND {$valueTable2}.attribute_id='{$attributeId}'" .
                " AND {$valueTable2}.store_id='{$collection->getStoreId()}'",
                []
            );

            $valueExpr = $collection->getConnection()->getCheckSql(
                $valueTable2 . '.value_id > 0',
                $valueTable2 . '.value',
                $valueTable1 . '.value'
            );
        }

        $collection->getSelect()->order($valueExpr . ' ' . $dir);

        return $this;
    }
}
