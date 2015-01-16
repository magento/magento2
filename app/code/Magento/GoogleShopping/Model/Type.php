<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model;

use Magento\Catalog\Model\Product as CatalogModelProduct;
use Magento\Framework\Gdata\Gshopping\Entry;

/**
 * Google Content Item Types Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Type extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Mapping attributes collection
     *
     * @var \Magento\GoogleShopping\Model\Resource\Attribute\Collection
     */
    protected $_attributesCollection;

    /**
     * @var \Magento\GoogleShopping\Helper\Product
     */
    protected $_gsProduct;

    /**
     * @var \Magento\GoogleShopping\Helper\Data
     */
    protected $_googleShoppingHelper;

    /**
     * Config
     *
     * @var \Magento\GoogleShopping\Model\Config
     */
    protected $_config;

    /**
     * Attribute factory
     *
     * @var \Magento\GoogleShopping\Model\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * Attribute collection factory
     *
     * @var \Magento\GoogleShopping\Model\Resource\Attribute\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\GoogleShopping\Model\Resource\Attribute\CollectionFactory $collectionFactory
     * @param \Magento\GoogleShopping\Model\AttributeFactory $attributeFactory
     * @param \Magento\GoogleShopping\Model\Config $config
     * @param \Magento\GoogleShopping\Helper\Product $gsProduct
     * @param \Magento\GoogleShopping\Helper\Data $googleShoppingHelper
     * @param \Magento\GoogleShopping\Model\Resource\Type $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\GoogleShopping\Model\Resource\Attribute\CollectionFactory $collectionFactory,
        \Magento\GoogleShopping\Model\AttributeFactory $attributeFactory,
        \Magento\GoogleShopping\Model\Config $config,
        \Magento\GoogleShopping\Helper\Product $gsProduct,
        \Magento\GoogleShopping\Helper\Data $googleShoppingHelper,
        \Magento\GoogleShopping\Model\Resource\Type $resource,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_attributeFactory = $attributeFactory;
        $this->_config = $config;
        $this->_gsProduct = $gsProduct;
        $this->_googleShoppingHelper = $googleShoppingHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\GoogleShopping\Model\Resource\Type');
    }

    /**
     * Load type model by Attribute Set Id and Target Country
     *
     * @param int $attributeSetId Attribute Set
     * @param string $targetCountry Two-letters country ISO code
     * @return $this
     */
    public function loadByAttributeSetId($attributeSetId, $targetCountry)
    {
        return $this->getResource()->loadByAttributeSetIdAndTargetCountry($this, $attributeSetId, $targetCountry);
    }

    /**
     * Prepare Entry data and attributes before saving in Google Content
     *
     * @param CatalogModelProduct $product
     * @param Entry $entry
     * @return Entry
     */
    public function convertProductToEntry($product, $entry)
    {
        $map = $this->_getAttributesMapByProduct($product);
        $base = $this->_getBaseAttributes();
        $attributes = array_merge($base, $map);

        $this->_removeNonexistentAttributes($entry, array_keys($attributes));

        foreach ($attributes as $name => $attribute) {
            $attribute->convertAttribute($product, $entry);
        }

        return $entry;
    }

    /**
     * Return Product attribute values array
     *
     * @param CatalogModelProduct $product
     * @return array Product attribute values
     */
    protected function _getAttributesMapByProduct(CatalogModelProduct $product)
    {
        $result = [];
        $group = $this->_config->getAttributeGroupsFlat();
        foreach ($this->_getAttributesCollection() as $attribute) {
            $productAttribute = $this->_gsProduct->getProductAttribute($product, $attribute->getAttributeId());

            if (!is_null($productAttribute)) {
                // define final attribute name
                if ($attribute->getGcontentAttribute()) {
                    $name = $attribute->getGcontentAttribute();
                } else {
                    $name = $this->_gsProduct->getAttributeLabel($productAttribute, $product->getStoreId());
                }

                if (!is_null($name)) {
                    $name = $this->_googleShoppingHelper->normalizeName($name);
                    if (isset($group[$name])) {
                        // if attribute is in the group
                        if (!isset($result[$group[$name]])) {
                            $result[$group[$name]] = $this->_attributeFactory->createAttribute($group[$name]);
                        }
                        // add group attribute to parent attribute
                        $result[$group[$name]]->addData(
                            [
                                'group_attribute_' . $name => $this->_attributeFactory->createAttribute(
                                    $name
                                )->addData(
                                    $attribute->getData()
                                ),
                            ]
                        );
                        unset($group[$name]);
                    } else {
                        if (!isset($result[$name])) {
                            $result[$name] = $this->_attributeFactory->createAttribute($name);
                        }
                        $result[$name]->addData($attribute->getData());
                    }
                }
            }
        }

        return $this->_initGroupAttributes($result);
    }

    /**
     * Retrun array with base attributes
     *
     * @return array
     */
    protected function _getBaseAttributes()
    {
        $names = $this->_config->getBaseAttributes();
        $attributes = [];
        foreach ($names as $name) {
            $attributes[$name] = $this->_attributeFactory->createAttribute($name);
        }

        return $this->_initGroupAttributes($attributes);
    }

    /**
     * Append to attributes array subattribute's models
     *
     * @param array $attributes
     * @return array
     */
    protected function _initGroupAttributes($attributes)
    {
        $group = $this->_config->getAttributeGroupsFlat();
        foreach ($group as $child => $parent) {
            if (isset($attributes[$parent]) && !isset($attributes[$parent]['group_attribute_' . $child])) {
                $attributes[$parent]->addData(
                    ['group_attribute_' . $child => $this->_attributeFactory->createAttribute($child)]
                );
            }
        }

        return $attributes;
    }

    /**
     * Retrieve type's attributes collection
     * It is protected, because only Type knows about its attributes
     *
     * @return \Magento\GoogleShopping\Model\Resource\Attribute\Collection
     */
    protected function _getAttributesCollection()
    {
        if (is_null($this->_attributesCollection)) {
            $this->_attributesCollection = $this->_collectionFactory->create()->addAttributeSetFilter(
                $this->getAttributeSetId(),
                $this->getTargetCountry()
            );
        }
        return $this->_attributesCollection;
    }

    /**
     * Remove attributes which were removed from mapping.
     *
     * @param Entry $entry
     * @param string[] $existAttributes
     * @return Entry
     */
    protected function _removeNonexistentAttributes($entry, $existAttributes)
    {
        // attributes which can't be removed
        $ignoredAttributes = [
            "id",
            "image_link",
            "content_language",
            "target_country",
            "expiration_date",
            "adult",
        ];

        $contentAttributes = $entry->getContentAttributes();
        foreach ($contentAttributes as $contentAttribute) {
            $name = $this->_googleShoppingHelper->normalizeName($contentAttribute->getName());
            if (!in_array($name, $ignoredAttributes) && !in_array($existAttributes, $existAttributes)) {
                $entry->removeContentAttribute($name);
            }
        }

        return $entry;
    }
}
