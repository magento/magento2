<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

/**
 * Interface AttributeDefaultValueInterface
 * Allows to manage attribute default value through interface
 * @api
 * @package Magento\Eav\Api\Data
 */
interface AttributeDefaultValueInterface
{
    const DEFAULT_VALUE = "default_value";

    /**
     * @param string $defaultValue
     * @return \Magento\Framework\Api\MetadataObjectInterface
     */
    public function setDefaultValue($defaultValue);

    /**
     * @return string
     */
    public function getDefaultValue();
}
