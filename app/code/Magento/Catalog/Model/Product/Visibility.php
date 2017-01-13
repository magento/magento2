<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Product visibilite model and attribute source model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\DB\Ddl\Table;

class Visibility extends \Magento\Framework\DataObject implements OptionSourceInterface
{
    const VISIBILITY_NOT_VISIBLE = 1;

    const VISIBILITY_IN_CATALOG = 2;

    const VISIBILITY_IN_SEARCH = 3;

    const VISIBILITY_BOTH = 4;

    /**
     * Reference to the attribute instance
     *
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $_attribute;

    /**
     * Eav entity attribute
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavEntityAttribute;

    /**
     * Construct
     *
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavEntityAttribute
     * @param array $data
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavEntityAttribute,
        array $data = []
    ) {
        $this->_eavEntityAttribute = $eavEntityAttribute;
        parent::__construct($data);
    }

    /**
     * Retrieve visible in catalog ids array
     *
     * @return string[]
     */
    public function getVisibleInCatalogIds()
    {
        return [self::VISIBILITY_IN_CATALOG, self::VISIBILITY_BOTH];
    }

    /**
     * Retrieve visible in search ids array
     *
     * @return string[]
     */
    public function getVisibleInSearchIds()
    {
        return [self::VISIBILITY_IN_SEARCH, self::VISIBILITY_BOTH];
    }

    /**
     * Retrieve visible in site ids array
     *
     * @return string[]
     */
    public function getVisibleInSiteIds()
    {
        return [self::VISIBILITY_IN_SEARCH, self::VISIBILITY_IN_CATALOG, self::VISIBILITY_BOTH];
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::VISIBILITY_NOT_VISIBLE => __('Not Visible Individually'),
            self::VISIBILITY_IN_CATALOG => __('Catalog'),
            self::VISIBILITY_IN_SEARCH => __('Search'),
            self::VISIBILITY_BOTH => __('Catalog, Search')
        ];
    }

    /**
     * Retrieve all options
     *
     * @return array
     */
    public static function getAllOption()
    {
        $options = self::getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
     * Retrieve all options
     *
     * @return array
     */
    public static function getAllOptions()
    {
        $res = [];
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * Retrieve option text
     *
     * @param int $optionId
     * @return string
     */
    public static function getOptionText($optionId)
    {
        $options = self::getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => true,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_SMALLINT,
                'nullable' => true,
                'comment' => 'Catalog Product Visibility ' . $attributeCode . ' column',
            ],
        ];
    }

    /**
     * Retrieve Indexes for Flat
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        return [];
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return \Magento\Framework\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->_eavEntityAttribute->getFlatUpdateSelect($this->getAttribute(), $store);
    }

    /**
     * Set attribute instance
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return $this
     */
    public function setAttribute($attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**
     * Get attribute instance
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public function getAttribute()
    {
        return $this->_attribute;
    }

    /**
     * Add Value Sort To Collection Select
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @param string $dir direction
     * @return $this
     */
    public function addValueSortToCollection($collection, $dir = 'asc')
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $attributeId = $this->getAttribute()->getId();
        $attributeTable = $this->getAttribute()->getBackend()->getTable();
        $linkField = $this->getAttribute()->getEntity()->getLinkField();

        if ($this->getAttribute()->isScopeGlobal()) {
            $tableName = $attributeCode . '_t';
            $collection->getSelect()->joinLeft(
                [$tableName => $attributeTable],
                "e.{$linkField}={$tableName}.{$linkField}" .
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
                "e.{$linkField}={$valueTable1}.{$linkField}" .
                " AND {$valueTable1}.attribute_id='{$attributeId}'" .
                " AND {$valueTable1}.store_id='0'",
                []
            )->joinLeft(
                [$valueTable2 => $attributeTable],
                "e.{$linkField}={$valueTable2}.{$linkField}" .
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

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
