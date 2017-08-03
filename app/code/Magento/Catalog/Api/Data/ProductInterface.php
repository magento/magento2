<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 2.0.0
 */
interface ProductInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const SKU = 'sku';

    const NAME = 'name';

    const PRICE = 'price';

    const WEIGHT = 'weight';

    const STATUS = 'status';

    const VISIBILITY = 'visibility';

    const ATTRIBUTE_SET_ID = 'attribute_set_id';

    const TYPE_ID = 'type_id';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    /**#@-*/

    /**
     * Product id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getId();

    /**
     * Set product id
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * Product sku
     *
     * @return string
     * @since 2.0.0
     */
    public function getSku();

    /**
     * Set product sku
     *
     * @param string $sku
     * @return $this
     * @since 2.0.0
     */
    public function setSku($sku);

    /**
     * Product name
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getName();

    /**
     * Set product name
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name);

    /**
     * Product attribute set id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getAttributeSetId();

    /**
     * Set product attribute set id
     *
     * @param int $attributeSetId
     * @return $this
     * @since 2.0.0
     */
    public function setAttributeSetId($attributeSetId);

    /**
     * Product price
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getPrice();

    /**
     * Set product price
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setPrice($price);

    /**
     * Product status
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getStatus();

    /**
     * Set product status
     *
     * @param int $status
     * @return $this
     * @since 2.0.0
     */
    public function setStatus($status);

    /**
     * Product visibility
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getVisibility();

    /**
     * Set product visibility
     *
     * @param int $visibility
     * @return $this
     * @since 2.0.0
     */
    public function setVisibility($visibility);

    /**
     * Product type id
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getTypeId();

    /**
     * Set product type id
     *
     * @param string $typeId
     * @return $this
     * @since 2.0.0
     */
    public function setTypeId($typeId);

    /**
     * Product created date
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getCreatedAt();

    /**
     * Set product created date
     *
     * @param string $createdAt
     * @return $this
     * @since 2.0.0
     */
    public function setCreatedAt($createdAt);

    /**
     * Product updated date
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getUpdatedAt();

    /**
     * Set product updated date
     *
     * @param string $updatedAt
     * @return $this
     * @since 2.0.0
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Product weight
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getWeight();

    /**
     * Set product weight
     *
     * @param float $weight
     * @return $this
     * @since 2.0.0
     */
    public function setWeight($weight);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Catalog\Api\Data\ProductExtensionInterface $extensionAttributes);

    /**
     * Get product links info
     *
     * @return \Magento\Catalog\Api\Data\ProductLinkInterface[]|null
     * @since 2.0.0
     */
    public function getProductLinks();

    /**
     * Set product links info
     *
     * @param \Magento\Catalog\Api\Data\ProductLinkInterface[] $links
     * @return $this
     * @since 2.0.0
     */
    public function setProductLinks(array $links = null);

    /**
     * Get list of product options
     *
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionInterface[]|null
     * @since 2.0.0
     */
    public function getOptions();

    /**
     * Set list of product options
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionInterface[] $options
     * @return $this
     * @since 2.0.0
     */
    public function setOptions(array $options = null);

    /**
     * Get media gallery entries
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface[]|null
     * @since 2.0.0
     */
    public function getMediaGalleryEntries();

    /**
     * Set media gallery entries
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface[] $mediaGalleryEntries
     * @return $this
     * @since 2.0.0
     */
    public function setMediaGalleryEntries(array $mediaGalleryEntries = null);

    /**
     * Gets list of product tier prices
     *
     * @return \Magento\Catalog\Api\Data\ProductTierPriceInterface[]|null
     * @since 2.0.0
     */
    public function getTierPrices();

    /**
     * Sets list of product tier prices
     *
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterface[] $tierPrices
     * @return $this
     * @since 2.0.0
     */
    public function setTierPrices(array $tierPrices = null);
}
