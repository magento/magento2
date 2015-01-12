<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Service\Entity;

class TestService
{
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
     * @param \Magento\Webapi\Service\Entity\Nested $nested
     * @return \Magento\Webapi\Service\Entity\Nested
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
     * @param \Magento\Webapi\Service\Entity\Simple[] $dataObjects
     * @return \Magento\Webapi\Service\Entity\Simple[]
     */
    public function dataArray(array $dataObjects)
    {
        return $dataObjects;
    }

    /**
     * @param \Magento\Webapi\Service\Entity\SimpleArray $arrayData
     * @return \Magento\Webapi\Service\Entity\SimpleArray
     */
    public function nestedSimpleArray(SimpleArray $arrayData)
    {
        return $arrayData;
    }

    /**
     * @param \Magento\Webapi\Service\Entity\AssociativeArray $associativeArrayData
     * @return \Magento\Webapi\Service\Entity\AssociativeArray
     */
    public function nestedAssociativeArray(AssociativeArray $associativeArrayData)
    {
        return $associativeArrayData;
    }

    /**
     * @param \Magento\Webapi\Service\Entity\DataArray $dataObjects
     * @return \Magento\Webapi\Service\Entity\DataArray
     */
    public function nestedDataArray(DataArray $dataObjects)
    {
        return $dataObjects;
    }
}
