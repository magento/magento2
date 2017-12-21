<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Webapi;

/**
 * Maps configured entity types relative to their service data contract
 */
class ServiceTypeToEntityTypeMap
{
    /**
     * @var string
     */
    private $serviceTypeToEntityTypeMap;

    /**
     * @param $serviceTypeToEntityTypeMap
     */
    public function __construct($serviceTypeToEntityTypeMap)
    {
        $this->serviceTypeToEntityTypeMap = $serviceTypeToEntityTypeMap;
    }

    /**
     * Return entity type mapped to service type. Returns false if service type is not mapped.
     *
     * Example:
     * [
     *   'Magento\Catalog\Api\Data\ProductInterface' => 'catalog_product'
     * ]
     *
     * @return string|boolean
     */
    public function getEntityType(string $serviceType)
    {
        if (isset($this->serviceTypeToEntityTypeMap[$serviceType])) {
            return $this->serviceTypeToEntityTypeMap[$serviceType];
        }

        return false;
    }
}
