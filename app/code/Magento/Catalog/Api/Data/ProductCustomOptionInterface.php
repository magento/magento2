<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * @api
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
     * @return string|null
     */
    public function getFileExtension();

    /**
     * @param string $fileExtension
     * @return $this
     */
    public function setFileExtension($fileExtension);

    /**
     * @return int|null
     */
    public function getMaxCharacters();

    /**
     * @param int $maxCharacters
     * @return $this
     */
    public function setMaxCharacters($maxCharacters);

    /**
     * @return int|null
     */
    public function getImageSizeX();

    /**
     * @param int $imageSizeX
     * @return $this
     */
    public function setImageSizeX($imageSizeX);

    /**
     * @return int|null
     */
    public function getImageSizeY();

    /**
     * @param int $imageSizeY
     * @return $this
     */
    public function setImageSizeY($imageSizeY);

    /**
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface[]|null
     */
    public function getValues();

    /**
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface[] $values
     * @return $this
     */
    public function setValues(array $values = null);

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
