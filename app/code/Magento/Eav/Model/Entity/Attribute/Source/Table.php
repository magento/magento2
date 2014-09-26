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
namespace Magento\Eav\Model\Entity\Attribute\Source;

class Table extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Default values for option cache
     *
     * @var array
     */
    protected $_optionsDefault = array();

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory
     */
    protected $_attrOptionCollectionFactory;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\OptionFactory
     */
    protected $_attrOptionFactory;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\OptionFactory $attrOptionFactory
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Eav\Model\Resource\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\Resource\Entity\Attribute\OptionFactory $attrOptionFactory
    ) {
        $this->_coreData = $coreData;
        $this->_attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->_attrOptionFactory = $attrOptionFactory;
    }

    /**
     * Retrieve Full Option values array
     *
     * @param bool $withEmpty       Add empty option to array
     * @param bool $defaultValues
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        $storeId = $this->getAttribute()->getStoreId();
        if (!is_array($this->_options)) {
            $this->_options = array();
        }
        if (!is_array($this->_optionsDefault)) {
            $this->_optionsDefault = array();
        }
        if (!isset($this->_options[$storeId])) {
            $collection = $this->_attrOptionCollectionFactory->create()->setPositionOrder(
                'asc'
            )->setAttributeFilter(
                $this->getAttribute()->getId()
            )->setStoreFilter(
                $this->getAttribute()->getStoreId()
            )->load();
            $this->_options[$storeId] = $collection->toOptionArray();
            $this->_optionsDefault[$storeId] = $collection->toOptionArray('default_value');
        }
        $options = $defaultValues ? $this->_optionsDefault[$storeId] : $this->_options[$storeId];
        if ($withEmpty) {
            array_unshift($options, array('label' => '', 'value' => ''));
        }

        return $options;
    }

    /**
     * Retrieve Option values array by ids
     *
     * @param string|array $ids
     * @param bool $withEmpty Add empty option to array
     * @return array
     */
    public function getSpecificOptions($ids, $withEmpty = true)
    {
        $options = $this->_attrOptionCollectionFactory->create()
            ->setPositionOrder('asc')
            ->setAttributeFilter($this->getAttribute()->getId())
            ->addFieldToFilter('main_table.option_id', array('in' => $ids))
            ->setStoreFilter($this->getAttribute()->getStoreId())
            ->load()
            ->toOptionArray();
        if ($withEmpty) {
            array_unshift($options, array('label' => '', 'value' => ''));
        }
        return $options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return array|string|bool
     */
    public function getOptionText($value)
    {
        $isMultiple = false;
        if (strpos($value, ',')) {
            $isMultiple = true;
            $value = explode(',', $value);
        }

        $options = $this->getSpecificOptions($value, false);

        if ($isMultiple) {
            $values = array();
            foreach ($options as $item) {
                if (in_array($item['value'], $value)) {
                    $values[] = $item['label'];
                }
            }
            return $values;
        }

        foreach ($options as $item) {
            if ($item['value'] == $value) {
                return $item['label'];
            }
        }
        return false;
    }

    /**
     * Add Value Sort To Collection Select
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @param string $dir
     *
     * @return $this
     */
    public function addValueSortToCollection($collection, $dir = \Magento\Framework\DB\Select::SQL_ASC)
    {
        $valueTable1 = $this->getAttribute()->getAttributeCode() . '_t1';
        $valueTable2 = $this->getAttribute()->getAttributeCode() . '_t2';
        $collection->getSelect()->joinLeft(
            array($valueTable1 => $this->getAttribute()->getBackend()->getTable()),
            "e.entity_id={$valueTable1}.entity_id" .
            " AND {$valueTable1}.attribute_id='{$this->getAttribute()->getId()}'" .
            " AND {$valueTable1}.store_id=0",
            array()
        )->joinLeft(
            array($valueTable2 => $this->getAttribute()->getBackend()->getTable()),
            "e.entity_id={$valueTable2}.entity_id" .
            " AND {$valueTable2}.attribute_id='{$this->getAttribute()->getId()}'" .
            " AND {$valueTable2}.store_id='{$collection->getStoreId()}'",
            array()
        );
        $valueExpr = $collection->getSelect()->getAdapter()->getCheckSql(
            "{$valueTable2}.value_id > 0",
            "{$valueTable2}.value",
            "{$valueTable1}.value"
        );

        $this->_attrOptionFactory->create()->addOptionValueToCollection(
            $collection,
            $this->getAttribute(),
            $valueExpr
        );

        $collection->getSelect()->order("{$this->getAttribute()->getAttributeCode()} {$dir}");

        return $this;
    }

    /**
     * Retrieve Column(s) for Flat
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $columns = array();
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $isMulti = $this->getAttribute()->getFrontend()->getInputType() == 'multiselect';

        $type = $isMulti ? \Magento\Framework\DB\Ddl\Table::TYPE_TEXT : \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER;
        $columns[$attributeCode] = array(
            'type' => $type,
            'length' => $isMulti ? '255' : null,
            'unsigned' => false,
            'nullable' => true,
            'default' => null,
            'extra' => null,
            'comment' => $attributeCode . ' column'
        );
        if (!$isMulti) {
            $columns[$attributeCode . '_value'] = array(
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'unsigned' => false,
                'nullable' => true,
                'default' => null,
                'extra' => null,
                'comment' => $attributeCode . ' column'
            );
        }

        return $columns;
    }

    /**
     * Retrieve Indexes for Flat
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        $indexes = array();

        $index = sprintf('IDX_%s', strtoupper($this->getAttribute()->getAttributeCode()));
        $indexes[$index] = array('type' => 'index', 'fields' => array($this->getAttribute()->getAttributeCode()));

        $sortable = $this->getAttribute()->getUsedForSortBy();
        if ($sortable && $this->getAttribute()->getFrontend()->getInputType() != 'multiselect') {
            $index = sprintf('IDX_%s_VALUE', strtoupper($this->getAttribute()->getAttributeCode()));

            $indexes[$index] = array(
                'type' => 'index',
                'fields' => array($this->getAttribute()->getAttributeCode() . '_value')
            );
        }

        return $indexes;
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return \Magento\Framework\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->_attrOptionFactory->create()->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}
