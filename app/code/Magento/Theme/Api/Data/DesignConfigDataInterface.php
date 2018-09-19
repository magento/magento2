<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Api\Data;

use Magento\Theme\Api\Data\DesignConfigDataExtensionInterface;

/**
 * Interface DesignConfigDataInterface
 * @api
 * @since 100.1.0
 */
interface DesignConfigDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const PATH = 'path';
    const VALUE = 'value';
    const FIELD_CONFIG = 'field_config';
    /**#@-*/

    /**
     * @return string
     * @since 100.1.0
     */
    public function getPath();

    /**
     * @return string
     * @since 100.1.0
     */
    public function getValue();

    /**
     * @return array
     * @since 100.1.0
     */
    public function getFieldConfig();

    /**
     * @param string $path
     * @return $this
     * @since 100.1.0
     */
    public function setPath($path);

    /**
     * @param string $value
     * @return $this
     * @since 100.1.0
     */
    public function setValue($value);

    /**
     * @param array $config
     * @return $this
     * @since 100.1.0
     */
    public function setFieldConfig(array $config);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Theme\Api\Data\DesignConfigDataExtensionInterface|null
     * @since 100.1.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Theme\Api\Data\DesignConfigDataExtensionInterface $extensionAttributes
     * @return $this
     * @since 100.1.0
     */
    public function setExtensionAttributes(DesignConfigDataExtensionInterface $extensionAttributes);
}
