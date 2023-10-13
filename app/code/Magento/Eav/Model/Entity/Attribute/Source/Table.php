<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Source;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Eav attribute default source when values are coming from another table
 *
 * @api
 * @since 100.0.2
 */
class Table extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource implements ResetAfterRequestInterface
{
    /**
     * Default values for option cache
     *
     * @var array
     */
    protected $_optionsDefault = [];

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $_attrOptionCollectionFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory
     */
    protected $_attrOptionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param StoreManagerInterface|null $storeManager
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        StoreManagerInterface $storeManager = null
    ) {
        $this->_attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->_attrOptionFactory = $attrOptionFactory;
        $this->storeManager = $storeManager ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);
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
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        if (!is_array($this->_options)) {
            $this->_options = [];
        }
        if (!is_array($this->_optionsDefault)) {
            $this->_optionsDefault = [];
        }
        $attributeId = $this->getAttribute()->getId();
        if (!isset($this->_options[$storeId][$attributeId])) {
            $collection = $this->_attrOptionCollectionFactory->create()->setPositionOrder(
                'asc'
            )->setAttributeFilter(
                $attributeId
            )->setStoreFilter(
                $storeId
            )->load();
            $this->_options[$storeId][$attributeId] = $collection->toOptionArray();
            $this->_optionsDefault[$storeId][$attributeId] = $collection->toOptionArray('default_value');
        }
        $options = $defaultValues
            ? $this->_optionsDefault[$storeId][$attributeId]
            : $this->_options[$storeId][$attributeId];
        if ($withEmpty) {
            $options = $this->addEmptyOption($options);
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
            ->addFieldToFilter('main_table.option_id', ['in' => $ids])
            ->setStoreFilter($this->getAttribute()->getStoreId())
            ->load()
            ->toOptionArray();
        if ($withEmpty) {
            $options = $this->addEmptyOption($options);
        }
        return $options;
    }

    /**
     * Add an empty option to the array
     *
     * @param array $options
     * @return array
     */
    private function addEmptyOption(array $options)
    {
        array_unshift($options, ['label' => ' ', 'value' => '']);
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
        if (is_string($value) && strpos($value, ',') !== false) {
            $isMultiple = true;
            $value = explode(',', $value);
        }

        $options = $this->getSpecificOptions($value, false);

        if ($isMultiple) {
            $values = [];
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
        $attribute = $this->getAttribute();
        $valueTable1 = $attribute->getAttributeCode() . '_t1';
        $valueTable2 = $attribute->getAttributeCode() . '_t2';
        $linkField = $attribute->getEntity()->getLinkField();
        $collection->getSelect()->joinLeft(
            [$valueTable1 => $attribute->getBackend()->getTable()],
            "e.{$linkField}={$valueTable1}." . $linkField .
            " AND {$valueTable1}.attribute_id='{$attribute->getId()}'" .
            " AND {$valueTable1}.store_id=0",
            []
        )->joinLeft(
            [$valueTable2 => $attribute->getBackend()->getTable()],
            "e.{$linkField}={$valueTable2}." . $linkField .
            " AND {$valueTable2}.attribute_id='{$attribute->getId()}'" .
            " AND {$valueTable2}.store_id='{$collection->getStoreId()}'",
            []
        );
        $valueExpr = $collection->getSelect()->getConnection()->getCheckSql(
            "{$valueTable2}.value_id > 0",
            "{$valueTable2}.value",
            "{$valueTable1}.value"
        );

        $this->_attrOptionFactory->create()->addOptionValueToCollection(
            $collection,
            $attribute,
            $valueExpr
        )->addOptionToCollection(
            $collection,
            $attribute,
            $valueExpr
        );

        $collection->getSelect()->order("{$attribute->getAttributeCode()}_order {$dir}");

        return $this;
    }

    /**
     * Retrieve Column(s) for Flat
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $columns = [];
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $isMulti = $this->getAttribute()->getFrontend()->getInputType() == 'multiselect';

        $type = $isMulti ? \Magento\Framework\DB\Ddl\Table::TYPE_TEXT : \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER;
        $columns[$attributeCode] = [
            'type' => $type,
            'length' => $isMulti ? '255' : null,
            'unsigned' => false,
            'nullable' => true,
            'default' => null,
            'extra' => null,
            'comment' => $attributeCode . ' column',
        ];
        if (!$isMulti) {
            $columns[$attributeCode . '_value'] = [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'unsigned' => false,
                'nullable' => true,
                'default' => null,
                'extra' => null,
                'comment' => $attributeCode . ' column',
            ];
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
        $indexes = [];

        $index = sprintf('IDX_%s', strtoupper($this->getAttribute()->getAttributeCode()));
        $indexes[$index] = ['type' => 'index', 'fields' => [$this->getAttribute()->getAttributeCode()]];

        $sortable = $this->getAttribute()->getUsedForSortBy();
        if ($sortable && $this->getAttribute()->getFrontend()->getInputType() != 'multiselect') {
            $index = sprintf('IDX_%s_VALUE', strtoupper($this->getAttribute()->getAttributeCode()));

            $indexes[$index] = [
                'type' => 'index',
                'fields' => [$this->getAttribute()->getAttributeCode() . '_value'],
            ];
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

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->_optionsDefault = [];
        $this->_options = null;
    }
}
