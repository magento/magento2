<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\ServiceInputProcessor;

class TestService
{
    public const DEFAULT_VALUE = 42;
    public const CUSTOM_ATTRIBUTE_CODE = 'customAttr';

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
     * @param SimpleImmutable $simpleImmutable
     * @return SimpleImmutable
     */
    public function simpleImmutable(SimpleImmutable $simpleImmutable)
    {
        return $simpleImmutable;
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
     * @param int $entityId
     * @return string[]
     */
    public function constructorArguments($entityId = self::DEFAULT_VALUE)
    {
        return [$entityId];
    }

    /**
     * @param Nested $nested
     * @return Nested
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
     * @param Simple[] $dataObjects
     * @return Simple[]
     */
    public function dataArray(array $dataObjects)
    {
        return $dataObjects;
    }

    /**
     * @param SimpleArray $arrayData
     * @return SimpleArray
     */
    public function nestedSimpleArray(SimpleArray $arrayData)
    {
        return $arrayData;
    }

    /**
     * @param AssociativeArray $associativeArrayData
     * @return AssociativeArray
     */
    public function nestedAssociativeArray(AssociativeArray $associativeArrayData)
    {
        return $associativeArrayData;
    }

    /**
     * @param DataArray $dataObjects
     * @return DataArray
     */
    public function nestedDataArray(DataArray $dataObjects)
    {
        return $dataObjects;
    }

    /**
     * @param ObjectWithCustomAttributes $param
     * @return ObjectWithCustomAttributes
     */
    public function objectWithCustomAttributesMethod(
        ObjectWithCustomAttributes $param
    ) {
        return $param;
    }
}
