<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * CatalogWidget Rule Product Condition data model
 */
namespace Magento\CatalogWidget\Model\Rule\Condition;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductCategoryList;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Store\Model\Store;

/**
 * Rule product condition data model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product extends \Magento\Rule\Model\Condition\Product\AbstractProduct
{
    /**
     * @var string
     */
    protected $elementName = 'parameters';

    /**
     * @var array
     */
    protected $joinedAttributes = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     * @param ProductCategoryList $categoryList
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = [],
        ProductCategoryList $categoryList = null
    ) {
        $this->storeManager = $storeManager;
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data,
            $categoryList
        );
    }

    /**
     * @inheritdoc
     */
    public function loadAttributeOptions()
    {
        $productAttributes = $this->_productResource->loadAllAttributes()->getAttributesByCode();
        $productAttributes = array_filter(
            $productAttributes,
            function ($attribute) {
                return $attribute->getFrontendLabel() &&
                    $attribute->getFrontendInput() !== 'text' &&
                    $attribute->getAttributeCode() !== ProductInterface::STATUS;
            }
        );

        $attributes = [];
        foreach ($productAttributes as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $this->_addSpecialAttributes($attributes);

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param array &$attributes
     * @return void
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['sku'] = __('SKU');
    }

    /**
     * Add condition to collection
     *
     * @param Collection $collection
     * @return $this
     */
    public function addToCollection($collection)
    {
        $attribute = $this->getAttributeObject();
        $attributeCode = $attribute->getAttributeCode();
        if ($attributeCode !== 'price' || !$collection->getLimitationFilters()->isUsingPriceIndex()) {
            if ($collection->isEnabledFlat()) {
                if ($attribute->isEnabledInFlat()) {
                    $alias = array_keys($collection->getSelect()->getPart('from'))[0];
                    $this->joinedAttributes[$attributeCode] = $alias . '.' . $attributeCode;
                } else {
                    $alias = 'at_' . $attributeCode;
                    if (!in_array($alias, array_keys($collection->getSelect()->getPart('from')))) {
                        $collection->joinAttribute($attributeCode, "catalog_product/$attributeCode", 'entity_id');
                    }

                    $this->joinedAttributes[$attributeCode] = $alias . '.value';
                }
            } elseif ($attributeCode !== 'category_ids' && !$attribute->isStatic()) {
                $this->addAttributeToCollection($attribute, $collection);
                $attributes = $this->getRule()->getCollectedAttributes();
                $attributes[$attributeCode] = true;
                $this->getRule()->setCollectedAttributes($attributes);
            }
        } else {
            $this->joinedAttributes['price'] ='price_index.min_price';
        }

        return $this;
    }

    /**
     * Adds Attributes that belong to Global Scope
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param Collection $collection
     * @return $this
     */
    protected function addGlobalAttribute(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute,
        Collection $collection
    ) {
        switch ($attribute->getBackendType()) {
            case 'decimal':
            case 'datetime':
            case 'int':
                $alias = 'at_' . $attribute->getAttributeCode();
                $collection->addAttributeToSelect($attribute->getAttributeCode(), 'inner');
                break;
            default:
                $alias = 'at_' . sha1($this->getId()) . $attribute->getAttributeCode();

                $connection = $this->_productResource->getConnection();
                $storeId = $connection->getIfNullSql($alias . '.store_id', $this->storeManager->getStore()->getId());
                $linkField = $attribute->getEntity()->getLinkField();

                $collection->getSelect()->join(
                    [$alias => $collection->getTable($attribute->getBackendTable())],
                    "($alias.$linkField = e.$linkField) AND ($alias.store_id = $storeId)" .
                    " AND ($alias.attribute_id = {$attribute->getId()})",
                    []
                );
        }

        $this->joinedAttributes[$attribute->getAttributeCode()] = $alias . '.value';

        return $this;
    }

    /**
     * Adds Attributes that don't belong to Global Scope
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param Collection $collection
     * @return $this
     */
    protected function addNotGlobalAttribute(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute,
        Collection $collection
    ) {
        $storeId = $this->storeManager->getStore()->getId();
        $values = $collection->getAllAttributeValues($attribute);
        $validEntities = [];
        if ($values) {
            foreach ($values as $entityId => $storeValues) {
                if (isset($storeValues[$storeId])) {
                    if ($this->validateAttribute($storeValues[$storeId])) {
                        $validEntities[] = $entityId;
                    }
                } else {
                    if (isset($storeValues[Store::DEFAULT_STORE_ID]) &&
                        $this->validateAttribute($storeValues[Store::DEFAULT_STORE_ID])
                    ) {
                        $validEntities[] = $entityId;
                    }
                }
            }
        }
        $this->setOperator('()');
        $this->unsetData('value_parsed');
        if ($validEntities) {
            $this->setData('value', implode(',', $validEntities));
        } else {
            $this->unsetData('value');
        }

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getMappedSqlField()
    {
        $result = '';
        if (in_array($this->getAttribute(), ['category_ids', 'sku', 'attribute_set_id'])) {
            $result = parent::getMappedSqlField();
        } elseif (isset($this->joinedAttributes[$this->getAttribute()])) {
            $result = $this->joinedAttributes[$this->getAttribute()];
        } elseif ($this->getAttributeObject()->isStatic()) {
            $result = $this->getAttributeObject()->getAttributeCode();
        } elseif ($this->getValueParsed()) {
            $result = 'e.entity_id';
        }

        return $result;
    }

    /**
     * @inheritdoc
     *
     * @param Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        return $this->addToCollection($productCollection);
    }

    /**
     * Add attribute to collection based on scope
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param Collection $collection
     * @return void
     */
    private function addAttributeToCollection($attribute, $collection): void
    {
        if ($attribute->getBackend() && $attribute->isScopeGlobal()) {
            $this->addGlobalAttribute($attribute, $collection);
        } else {
            $this->addNotGlobalAttribute($attribute, $collection);
        }
    }

    /**
     * @inheritdoc
     */
    public function getBindArgumentValue()
    {
        $value = parent::getBindArgumentValue();
        return is_array($value) && $this->getMappedSqlField() === 'e.entity_id'
            ? new \Zend_Db_Expr(
                $this->_productResource->getConnection()->quoteInto('?', $value, \Zend_Db::INT_TYPE)
            )
            : $value;
    }
}
