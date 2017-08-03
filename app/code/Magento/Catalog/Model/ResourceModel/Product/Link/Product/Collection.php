<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Link\Product;

/**
 * Catalog product linked products collection
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * Store product model
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * Store product link model
     *
     * @var \Magento\Catalog\Model\Product\Link
     */
    protected $_linkModel;

    /**
     * Store link type id
     *
     * @var int
     */
    protected $_linkTypeId;

    /**
     * Store strong mode flag that determine if needed for inner join or left join of linked products
     *
     * @var bool
     */
    protected $_isStrongMode;

    /**
     * Store flag that determine if product filter was enabled
     *
     * @var bool
     */
    protected $_hasLinkFilter = false;

    /**
     * Declare link model and initialize type attributes join
     *
     * @param \Magento\Catalog\Model\Product\Link $linkModel
     * @return $this
     */
    public function setLinkModel(\Magento\Catalog\Model\Product\Link $linkModel)
    {
        $this->_linkModel = $linkModel;
        if ($linkModel->getLinkTypeId()) {
            $this->_linkTypeId = $linkModel->getLinkTypeId();
        }
        return $this;
    }

    /**
     * Enable strong mode for inner join of linked products
     *
     * @return $this
     */
    public function setIsStrongMode()
    {
        $this->_isStrongMode = true;
        return $this;
    }

    /**
     * Retrieve collection link model
     *
     * @return \Magento\Catalog\Model\Product\Link
     */
    public function getLinkModel()
    {
        return $this->_linkModel;
    }

    /**
     * Initialize collection parent product and add limitation join
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function setProduct(\Magento\Catalog\Model\Product $product)
    {
        $this->_product = $product;
        if ($product && $product->getId()) {
            $this->_hasLinkFilter = true;
            $this->setStore($product->getStore());
        }
        return $this;
    }

    /**
     * Retrieve collection base product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * Exclude products from filter
     *
     * @param array $products
     * @return $this
     */
    public function addExcludeProductFilter($products)
    {
        if (!empty($products)) {
            if (!is_array($products)) {
                $products = [$products];
            }
            $this->_hasLinkFilter = true;
            $this->getSelect()->where('links.linked_product_id NOT IN (?)', $products);
        }
        return $this;
    }

    /**
     * Add products to filter
     *
     * @param array|int|string $products
     * @return $this
     */
    public function addProductFilter($products)
    {
        if (!empty($products)) {
            if (!is_array($products)) {
                $products = [$products];
            }
            $identifierField = $this->getProductEntityMetadata()->getIdentifierField();
            $this->getSelect()->where("product_entity_table.$identifierField IN (?)", $products);
            $this->_hasLinkFilter = true;
        }

        return $this;
    }

    /**
     * Add random sorting order
     *
     * @return $this
     */
    public function setRandomOrder()
    {
        $this->getSelect()->orderRand('main_table.entity_id');
        return $this;
    }

    /**
     * Setting group by to exclude duplications in collection
     *
     * @param string $groupBy
     * @return $this
     */
    public function setGroupBy($groupBy = 'e.entity_id')
    {
        $this->getSelect()->group($groupBy);
        return $this;
    }

    /**
     * Join linked products when specified link model
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        if ($this->getLinkModel()) {
            $this->_joinLinks();
            $this->joinProductsToLinks();
        }
        return parent::_beforeLoad();
    }

    /**
     * Join linked products and their attributes
     *
     * @return $this
     */
    protected function _joinLinks()
    {
        $select = $this->getSelect();
        $connection = $select->getConnection();

        $joinCondition = [
            'links.linked_product_id = e.entity_id',
            $connection->quoteInto('links.link_type_id = ?', $this->_linkTypeId),
        ];
        $joinType = 'join';
        $linkField = $this->getProductEntityMetadata()->getLinkField();
        if ($this->getProduct() && $this->getProduct()->getId()) {
            $linkFieldId = $this->getProduct()->getData(
                $linkField
            );
            if ($this->_isStrongMode) {
                $this->getSelect()->where('links.product_id = ?', (int)$linkFieldId);
            } else {
                $joinType = 'joinLeft';
                $joinCondition[] = $connection->quoteInto('links.product_id = ?', $linkFieldId);
            }
            $this->addFieldToFilter(
                $linkField,
                ['neq' => $linkFieldId]
            );
        } elseif ($this->_isStrongMode) {
            $this->addFieldToFilter(
                $linkField,
                ['eq' => -1]
            );
        }
        if ($this->_hasLinkFilter) {
            $select->{$joinType}(
                ['links' => $this->getTable('catalog_product_link')],
                implode(' AND ', $joinCondition),
                ['link_id']
            );
            $this->joinAttributes();
        }
        return $this;
    }

    /**
     * Enable sorting products by its position
     *
     * @param string $dir sort type asc|desc
     * @return $this
     */
    public function setPositionOrder($dir = self::SORT_ORDER_ASC)
    {
        if ($this->_hasLinkFilter) {
            $this->getSelect()->order('position ' . $dir);
        }
        return $this;
    }

    /**
     * Enable sorting products by its attribute set name
     *
     * @param string $dir sort type asc|desc
     * @return $this
     */
    public function setAttributeSetIdOrder($dir = self::SORT_ORDER_ASC)
    {
        $this->getSelect()->joinLeft(
            ['set' => $this->getTable('eav_attribute_set')],
            'e.attribute_set_id = set.attribute_set_id',
            ['attribute_set_name']
        )->order(
            'set.attribute_set_name ' . $dir
        );
        return $this;
    }

    /**
     * Join attributes
     *
     * @return $this
     */
    public function joinAttributes()
    {
        if (!$this->getLinkModel()) {
            return $this;
        }

        foreach ($this->getLinkAttributes() as $attribute) {
            $table = $this->getLinkModel()->getAttributeTypeTable($attribute['type']);
            $alias = sprintf('link_attribute_%s_%s', $attribute['code'], $attribute['type']);

            $joinCondiotion = [
                "{$alias}.link_id = links.link_id",
                $this->getSelect()->getConnection()
                    ->quoteInto("{$alias}.product_link_attribute_id = ?", $attribute['id']),
            ];
            $this->getSelect()->joinLeft(
                [$alias => $table],
                implode(' AND ', $joinCondiotion),
                [$attribute['code'] => 'value']
            );
        }

        return $this;
    }

    /**
     * Set sorting order
     *
     * $attribute can also be an array of attributes
     *
     * @param string|array $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = self::SORT_ORDER_ASC)
    {
        if ($attribute == 'position') {
            return $this->setPositionOrder($dir);
        } elseif ($attribute == 'attribute_set_id') {
            return $this->setAttributeSetIdOrder($dir);
        }
        return parent::setOrder($attribute, $dir);
    }

    /**
     * Get attributes of specified link type
     *
     * @param int $type
     * @return array
     */
    public function getLinkAttributes($type = null)
    {
        return $this->getLinkModel()->getAttributes($type);
    }

    /**
     * Add link attribute to filter.
     *
     * @param string $code
     * @param array $condition
     * @return $this
     */
    public function addLinkAttributeToFilter($code, $condition)
    {
        foreach ($this->getLinkAttributes() as $attribute) {
            if ($attribute['code'] == $code) {
                $alias = sprintf('link_attribute_%s_%s', $code, $attribute['type']);
                $whereCondition = $this->_getConditionSql($alias . '.`value`', $condition);
                $this->getSelect()->where($whereCondition);
            }
        }
        return $this;
    }

    /**
     * Join Product To Links
     * @return void
     * @since 2.1.0
     */
    private function joinProductsToLinks()
    {
        if ($this->_hasLinkFilter) {
            $metaDataPool = $this->getProductEntityMetadata();
            $linkField = $metaDataPool->getLinkField();
            $entityTable = $metaDataPool->getEntityTable();
            $this->getSelect()
                ->join(
                    ['product_entity_table' => $entityTable],
                    "links.product_id = product_entity_table.$linkField",
                    []
                );
        }
    }
}
