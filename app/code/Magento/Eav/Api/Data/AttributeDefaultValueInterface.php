<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

/**
 * Interface AttributeDefaultValueInterface
 * Allows to manage attribute default value through interface
 * @api
 * @package Magento\Eav\Api\Data
 * @since 2.2.0
 */
interface AttributeDefaultValueInterface
{
    const DEFAULT_VALUE = "default_value";

    /**
     * @param string $defaultValue
     * @return \Magento\Framework\Api\MetadataObjectInterface
     * @since 2.2.0
     */
    public function setDefaultValue($defaultValue);

    /**
     * @return string
     * @since 2.2.0
     */
    public function getDefaultValue();
}
