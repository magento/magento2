<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\ServiceInputProcessor;

use Magento\Framework\Webapi\ServiceInputProcessor\AssociativeArray;
use Magento\Framework\Webapi\ServiceInputProcessor\DataArray;
use Magento\Framework\Webapi\ServiceInputProcessor\Nested;
use Magento\Framework\Webapi\ServiceInputProcessor\SimpleArray;

class TestService
{
    const DEFAULT_VALUE = 42;
    const CUSTOM_ATTRIBUTE_CODE = 'customAttr';

    /**
     * @param int $entityId
     * @param string $name
     * @return string[]
     */
    public function simple($entityId, $name)
    {
        return [$entityId, $name];
    }

    /**
     * @param int $entityId
     * @return string[]
     */
    public function simpleDefaultValue($entityId = self::DEFAULT_VALUE)
    {
        return [$entityId];
    }

    /**
     * @param \Magento\Framework\Webapi\ServiceInputProcessor\Nested $nested
     * @return \Magento\Framework\Webapi\ServiceInputProcessor\Nested
     */
    public function nestedData(Nested $nested)
    {
        return $nested;
    }

    /**
     * @param int[] $ids
     * @return int[]
     */
    public function simpleArray(array $ids)
    {
        return $ids;
    }

    /**
     * @param string[] $associativeArray
     * @return string[]
     */
    public function associativeArray(array $associativeArray)
    {
        return $associativeArray;
    }

    /**
     * @param \Magento\Framework\Webapi\ServiceInputProcessor\Simple[] $dataObjects
     * @return \Magento\Framework\Webapi\ServiceInputProcessor\Simple[]
     */
    public function dataArray(array $dataObjects)
    {
        return $dataObjects;
    }

    /**
     * @param \Magento\Framework\Webapi\ServiceInputProcessor\SimpleArray $arrayData
     * @return \Magento\Framework\Webapi\ServiceInputProcessor\SimpleArray
     */
    public function nestedSimpleArray(SimpleArray $arrayData)
    {
        return $arrayData;
    }

    /**
     * @param \Magento\Framework\Webapi\ServiceInputProcessor\AssociativeArray $associativeArrayData
     * @return \Magento\Framework\Webapi\ServiceInputProcessor\AssociativeArray
     */
    public function nestedAssociativeArray(AssociativeArray $associativeArrayData)
    {
        return $associativeArrayData;
    }

    /**
     * @param \Magento\Framework\Webapi\ServiceInputProcessor\DataArray $dataObjects
     * @return \Magento\Framework\Webapi\ServiceInputProcessor\DataArray
     */
    public function nestedDataArray(DataArray $dataObjects)
    {
        return $dataObjects;
    }

    /**
     * @param \Magento\Framework\Webapi\ServiceInputProcessor\ObjectWithCustomAttributes $param
     * @return \Magento\Framework\Webapi\ServiceInputProcessor\ObjectWithCustomAttributes
     */
    public function objectWithCustomAttributesMethod(
        \Magento\Framework\Webapi\ServiceInputProcessor\ObjectWithCustomAttributes $param
    ) {
        return $param;
    }
}
