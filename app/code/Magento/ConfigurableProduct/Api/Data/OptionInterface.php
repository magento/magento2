<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api\Data;

/**
 * Interface OptionInterface
 * @api
 * @since 2.0.0
 */
interface OptionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return int|null
     * @since 2.0.0
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * @return string|null
     * @since 2.0.0
     */
    public function getAttributeId();

    /**
     * @param string $attributeId
     * @return $this
     * @since 2.0.0
     */
    public function setAttributeId($attributeId);

    /**
     * @return string|null
     * @since 2.0.0
     */
    public function getLabel();

    /**
     * @param string $label
     * @return $this
     * @since 2.0.0
     */
    public function setLabel($label);

    /**
     * @return int|null
     * @since 2.0.0
     */
    public function getPosition();

    /**
     * @param int $position
     * @return $this
     * @since 2.0.0
     */
    public function setPosition($position);

    /**
     * @return bool|null
     * @since 2.0.0
     */
    public function getIsUseDefault();

    /**
     * @param bool $isUseDefault
     * @return $this
     * @since 2.0.0
     */
    public function setIsUseDefault($isUseDefault);

    /**
     * @return \Magento\ConfigurableProduct\Api\Data\OptionValueInterface[]|null
     * @since 2.0.0
     */
    public function getValues();

    /**
     * @param \Magento\ConfigurableProduct\Api\Data\OptionValueInterface[] $values
     * @return $this
     * @since 2.0.0
     */
    public function setValues(array $values = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\ConfigurableProduct\Api\Data\OptionExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\ConfigurableProduct\Api\Data\OptionExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\ConfigurableProduct\Api\Data\OptionExtensionInterface $extensionAttributes
    );

    /**
     * @return int|null
     * @since 2.0.0
     */
    public function getProductId();

    /**
     * @param int|null $value
     * @return $this
     * @since 2.0.0
     */
    public function setProductId($value);
}
