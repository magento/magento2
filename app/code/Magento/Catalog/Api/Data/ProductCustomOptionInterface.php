<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 100.0.2
 */
interface ProductCustomOptionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Product text options group.
     */
    public const OPTION_GROUP_TEXT = 'text';

    /**
     * Product file options group.
     */
    public const OPTION_GROUP_FILE = 'file';

    /**
     * Product select options group.
     */
    public const OPTION_GROUP_SELECT = 'select';

    /**
     * Product date options group.
     */
    public const OPTION_GROUP_DATE = 'date';

    /**
     * Product field option type.
     */
    public const OPTION_TYPE_FIELD = 'field';

    /**
     * Product area option type.
     */
    public const OPTION_TYPE_AREA = 'area';

    /**
     * Product file option type.
     */
    public const OPTION_TYPE_FILE = 'file';

    /**
     * Product drop-down option type.
     */
    public const OPTION_TYPE_DROP_DOWN = 'drop_down';

    /**
     * Product radio option type.
     */
    public const OPTION_TYPE_RADIO = 'radio';

    /**
     * Product checkbox option type.
     */
    public const OPTION_TYPE_CHECKBOX = 'checkbox';

    /**
     * Product multiple option type.
     */
    public const OPTION_TYPE_MULTIPLE = 'multiple';

    /**
     * Product date option type.
     */
    public const OPTION_TYPE_DATE = 'date';

    /**
     * Product datetime option type.
     */
    public const OPTION_TYPE_DATE_TIME = 'date_time';

    /**
     * Product time option type.
     */
    public const OPTION_TYPE_TIME = 'time';

    /**
     * Get product SKU
     *
     * @return string
     */
    public function getProductSku();

    /**
     * Set product SKU
     *
     * @param string $sku
     * @return $this
     */
    public function setProductSku($sku);

    /**
     * Get option id
     *
     * @return int|null
     */
    public function getOptionId();

    /**
     * Set option id
     *
     * @param int $optionId
     * @return $this
     */
    public function setOptionId($optionId);

    /**
     * Get option title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set option title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Get option type
     *
     * @return string
     */
    public function getType();

    /**
     * Set option type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * Get is require
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsRequire();

    /**
     * Set is require
     *
     * @param bool $isRequired
     * @return $this
     */
    public function setIsRequire($isRequired);

    /**
     * Get price
     *
     * @return float|null
     */
    public function getPrice();

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Get price type
     *
     * @return string|null
     */
    public function getPriceType();

    /**
     * Set price type
     *
     * @param string $priceType
     * @return $this
     */
    public function setPriceType($priceType);

    /**
     * Get Sku
     *
     * @return string|null
     */
    public function getSku();

    /**
     * Set Sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get File extension
     *
     * @return string|null
     */
    public function getFileExtension();

    /**
     * Set File extension
     *
     * @param string $fileExtension
     * @return $this
     */
    public function setFileExtension($fileExtension);

    /**
     * Get Max characters
     *
     * @return int|null
     */
    public function getMaxCharacters();

    /**
     * Set Max characters
     *
     * @param int $maxCharacters
     * @return $this
     */
    public function setMaxCharacters($maxCharacters);

    /**
     * Get Image x size
     *
     * @return int|null
     */
    public function getImageSizeX();

    /**
     * Set Image x size
     *
     * @param int $imageSizeX
     * @return $this
     */
    public function setImageSizeX($imageSizeX);

    /**
     * Get Image Y size
     *
     * @return int|null
     */
    public function getImageSizeY();

    /**
     * Set Image Y size
     *
     * @param int $imageSizeY
     * @return $this
     */
    public function setImageSizeY($imageSizeY);

    /**
     * Get Values
     *
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface[]|null
     */
    public function getValues();

    /**
     * Set Values
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface[] $values
     * @return $this
     */
    public function setValues(?array $values = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductCustomOptionExtensionInterface $extensionAttributes
    );
}
