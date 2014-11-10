<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        return array($entityId, $name);
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
