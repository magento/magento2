<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\TypeLocator;

class ServiceTypeToEntityTypeMap
{
    /**
     * @var string[]
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
     * @return string[]
     */
    public function getMap()
    {
        return $this->serviceTypeToEntityTypeMap;
    }
}
