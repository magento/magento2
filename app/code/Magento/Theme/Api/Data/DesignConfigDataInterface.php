<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Api\Data;

use Magento\Theme\Api\Data\DesignConfigDataExtensionInterface;

/**
 * Interface DesignConfigDataInterface
 * @api
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
     */
    public function getPath();

    /**
     * @return string
     */
    public function getValue();

    /**
     * @return array
     */
    public function getFieldConfig();

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path);

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value);

    /**
     * @param array $config
     * @return $this
     */
    public function setFieldConfig(array $config);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return DesignConfigDataExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param DesignConfigDataExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(DesignConfigDataExtensionInterface $extensionAttributes);
}
