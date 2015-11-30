<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Api\Data;

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
}
