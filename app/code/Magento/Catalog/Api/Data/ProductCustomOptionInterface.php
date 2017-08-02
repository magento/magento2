<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 2.0.0
 */
interface ProductCustomOptionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Product text options group.
     */
    const OPTION_GROUP_TEXT = 'text';

    /**
     * Product file options group.
     */
    const OPTION_GROUP_FILE = 'file';

    /**
     * Product select options group.
     */
    const OPTION_GROUP_SELECT = 'select';

    /**
     * Product date options group.
     */
    const OPTION_GROUP_DATE = 'date';

    /**
     * Product field option type.
     */
    const OPTION_TYPE_FIELD = 'field';

    /**
     * Product area option type.
     */
    const OPTION_TYPE_AREA = 'area';

    /**
     * Product file option type.
     */
    const OPTION_TYPE_FILE = 'file';

    /**
     * Product drop-down option type.
     */
    const OPTION_TYPE_DROP_DOWN = 'drop_down';

    /**
     * Product radio option type.
     */
    const OPTION_TYPE_RADIO = 'radio';

    /**
     * Product checkbox option type.
     */
    const OPTION_TYPE_CHECKBOX = 'checkbox';

    /**
     * Product multiple option type.
     */
    const OPTION_TYPE_MULTIPLE = 'multiple';

    /**
     * Product date option type.
     */
    const OPTION_TYPE_DATE = 'date';

    /**
     * Product datetime option type.
     */
    const OPTION_TYPE_DATE_TIME = 'date_time';

    /**
     * Product time option type.
     */
    const OPTION_TYPE_TIME = 'time';

    /**
     * Get product SKU
     *
     * @return string
     * @since 2.0.0
     */
    public function getProductSku();

    /**
     * Set product SKU
     *
     * @param string $sku
     * @return $this
     * @since 2.0.0
     */
    public function setProductSku($sku);

    /**
     * Get option id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getOptionId();

    /**
     * Set option id
     *
     * @param int $optionId
     * @return $this
     * @since 2.0.0
     */
    public function setOptionId($optionId);

    /**
     * Get option title
     *
     * @return string
     * @since 2.0.0
     */
    public function getTitle();

    /**
     * Set option title
     *
     * @param string $title
     * @return $this
     * @since 2.0.0
     */
    public function setTitle($title);

    /**
     * Get option type
     *
     * @return string
     * @since 2.0.0
     */
    public function getType();

    /**
     * Set option type
     *
     * @param string $type
     * @return $this
     * @since 2.0.0
     */
    public function setType($type);

    /**
     * Get sort order
     *
     * @return int
     * @since 2.0.0
     */
    public function getSortOrder();

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     * @since 2.0.0
     */
    public function setSortOrder($sortOrder);

    /**
     * Get is require
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getIsRequire();

    /**
     * Set is require
     *
     * @param bool $isRequired
     * @return $this
     * @since 2.0.0
     */
    public function setIsRequire($isRequired);

    /**
     * Get price
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getPrice();

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setPrice($price);

    /**
     * Get price type
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getPriceType();

    /**
     * Set price type
     *
     * @param string $priceType
     * @return $this
     * @since 2.0.0
     */
    public function setPriceType($priceType);

    /**
     * Get Sku
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getSku();

    /**
     * Set Sku
     *
     * @param string $sku
     * @return $this
     * @since 2.0.0
     */
    public function setSku($sku);

    /**
     * @return string|null
     * @since 2.0.0
     */
    public function getFileExtension();

    /**
     * @param string $fileExtension
     * @return $this
     * @since 2.0.0
     */
    public function setFileExtension($fileExtension);

    /**
     * @return int|null
     * @since 2.0.0
     */
    public function getMaxCharacters();

    /**
     * @param int $maxCharacters
     * @return $this
     * @since 2.0.0
     */
    public function setMaxCharacters($maxCharacters);

    /**
     * @return int|null
     * @since 2.0.0
     */
    public function getImageSizeX();

    /**
     * @param int $imageSizeX
     * @return $this
     * @since 2.0.0
     */
    public function setImageSizeX($imageSizeX);

    /**
     * @return int|null
     * @since 2.0.0
     */
    public function getImageSizeY();

    /**
     * @param int $imageSizeY
     * @return $this
     * @since 2.0.0
     */
    public function setImageSizeY($imageSizeY);

    /**
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface[]|null
     * @since 2.0.0
     */
    public function getValues();

    /**
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface[] $values
     * @return $this
     * @since 2.0.0
     */
    public function setValues(array $values = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductCustomOptionExtensionInterface $extensionAttributes
    );
}
