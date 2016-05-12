<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Webapi;

/**
 * Interface to locate types for custom attributes
 */
interface CustomAttributeTypeLocatorInterface
{
    /**
     * Get Data Interface type for a given custom attribute code
     *
     * @param string $attributeCode
     * @param string $serviceClass
     * @return string
     */
    public function getType($attributeCode, $serviceClass);

    /**
     * Get list of all Data Interface corresponding to complex custom attribute types
     *
     * @return string[] array of Data Interface class names
     */
    public function getAllServiceDataInterfaces();
}
