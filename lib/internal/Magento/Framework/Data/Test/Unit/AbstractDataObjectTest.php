<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit;

class AbstractDataObjectTest extends \PHPUnit\Framework\TestCase
{
    public function testToArray()
    {
        $subObjectData = ['subKey' => 'subValue'];
        $nestedObjectData = ['nestedKey' => 'nestedValue'];
        $result = [
            'key' => 'value',
            'object' => $subObjectData,
            'nestedArray' => ['nestedObject' => $nestedObjectData],
        ];

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $subObject = $objectManager->getObject(\Magento\Framework\Data\Test\Unit\Stub\DataObject::class);
        $subObject->setData($subObjectData);

        $nestedObject = $objectManager->getObject(\Magento\Framework\Data\Test\Unit\Stub\DataObject::class);
        $nestedObject->setData($nestedObjectData);

        $dataObject = $objectManager->getObject(\Magento\Framework\Data\Test\Unit\Stub\DataObject::class);
        $data = ['key' => 'value', 'object' => $subObject, 'nestedArray' => ['nestedObject' => $nestedObject]];
        $dataObject->setData($data);

        $this->assertEquals($result, $dataObject->toArray());
    }

    public function testGet()
    {
        $key = 'key';
        $value = 'value';
        $data = [$key => $value];

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $dataObject = $objectManager->getObject(\Magento\Framework\Data\Test\Unit\Stub\DataObject::class);
        $dataObject->setData($data);

        $this->assertEquals($value, $dataObject->get($key));
    }
}
