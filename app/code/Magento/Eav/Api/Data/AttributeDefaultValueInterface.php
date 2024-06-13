<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Eav\Api\Data;

/**
 * Interface AttributeDefaultValueInterface
 * Allows to manage attribute default value through interface
 * @api
 * @package Magento\Eav\Api\Data
 * @since 101.0.0
 */
interface AttributeDefaultValueInterface
{
    const DEFAULT_VALUE = "default_value";

    /**
     * @param string $defaultValue
     * @return \Magento\Framework\Api\MetadataObjectInterface
     * @since 101.0.0
     */
    public function setDefaultValue($defaultValue);

    /**
     * @return string
     * @since 101.0.0
     */
    public function getDefaultValue();
}
