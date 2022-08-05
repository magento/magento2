<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi;

/**
 * Interface to locate types for custom attributes
 *
 * @api
 */
interface CustomAttributeTypeLocatorInterface
{
    /**
     * Get Data Interface type for a given custom attribute code
     *
     * @param string $attributeCode
     * @param string $entityType
     * @return string
     */
    public function getType($attributeCode, $entityType);

    /**
     * Get list of all Data Interface corresponding to complex custom attribute types
     *
     * @return string[] array of Data Interface class names
     * @deprecated 102.0.0
     * @see \Magento\Framework\Webapi\CustomAttribute\ServiceTypeListInterface::getDataTypes()
     */
    public function getAllServiceDataInterfaces();
}
