<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model;

/**
 * Attributes Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Attribute extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Default ignored attribute codes
     *
     * @var string[]
     */
    protected $_ignoredAttributeCodes = [
        'custom_design',
        'custom_design_from',
        'custom_design_to',
        'custom_layout_update',
        'gift_message_available',
        'giftcard_amounts',
        'news_from_date',
        'news_to_date',
        'options_container',
        'price_view',
        'sku_type',
        'use_config_is_redeemable',
        'use_config_allow_message',
        'use_config_lifetime',
        'use_config_email_template',
        'tier_price',
        'minimal_price',
        'shipment_type'
    ];

    /**
     * Default ignored attribute types
     *
     * @var string[]
     */
    protected $_ignoredAttributeTypes = ['hidden', 'media_image', 'image', 'gallery'];

    /**
     * @var \Magento\GoogleShopping\Helper\Data|null
     */
    protected $_googleShoppingHelper = null;

    /**
     * @var \Magento\GoogleShopping\Helper\Product|null
     */
    protected $_gsProduct = null;

    /**
     * @var \Magento\Catalog\Model\Product\CatalogPrice
     */
    protected $catalogPrice;

    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\GoogleShopping\Helper\Data $googleShoppingHelper
     * @param \Magento\GoogleShopping\Helper\Product $gsProduct
     * @param \Magento\Catalog\Model\Product\CatalogPrice $catalogPrice
     * @param \Magento\GoogleShopping\Model\Resource\Attribute $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\GoogleShopping\Helper\Data $googleShoppingHelper,
        \Magento\GoogleShopping\Helper\Product $gsProduct,
        \Magento\Catalog\Model\Product\CatalogPrice $catalogPrice,
        \Magento\GoogleShopping\Model\Resource\Attribute $resource,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_googleShoppingHelper = $googleShoppingHelper;
        $this->_gsProduct = $gsProduct;
        $this->catalogPrice = $catalogPrice;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\GoogleShopping\Model\Resource\Attribute');
    }

    /**
     * Get array with allowed product attributes (for mapping) by selected attribute set
     *
     * @param int $setId attribute set id
     * @return array
     */
    public function getAllowedAttributes($setId)
    {
        $attributes = $this->_productFactory->create()->getResource()->loadAllAttributes()->getSortedAttributes(
            $setId
        );

        $titles = [];
        foreach ($attributes as $attribute) {
            /* @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
            if ($attribute->isInSet($setId) && $this->_isAllowedAttribute($attribute)) {
                $list[$attribute->getAttributeId()] = $attribute;
                $titles[$attribute->getAttributeId()] = $attribute->getFrontendLabel();
            }
        }
        asort($titles);
        $result = [];
        foreach ($titles as $attributeId => $label) {
            $result[$attributeId] = $list[$attributeId];
        }
        return $result;
    }

    /**
     * Check if attribute allowed
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return bool
     */
    protected function _isAllowedAttribute($attribute)
    {
        return !in_array(
            $attribute->getFrontendInput(),
            $this->_ignoredAttributeTypes
        ) && !in_array(
            $attribute->getAttributeCode(),
            $this->_ignoredAttributeCodes
        ) && $attribute->getFrontendLabel() != "";
    }
}
