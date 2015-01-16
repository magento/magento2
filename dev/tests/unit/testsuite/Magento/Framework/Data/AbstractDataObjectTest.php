<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

class AbstractDataObjectTest extends \PHPUnit_Framework_TestCase
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

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $subObject = $objectManager->getObject('Magento\Framework\Data\Stub\DataObject');
        $subObject->setData($subObjectData);

        $nestedObject = $objectManager->getObject('Magento\Framework\Data\Stub\DataObject');
        $nestedObject->setData($nestedObjectData);

        $dataObject = $objectManager->getObject('Magento\Framework\Data\Stub\DataObject');
        $data = ['key' => 'value', 'object' => $subObject, 'nestedArray' => ['nestedObject' => $nestedObject]];
        $dataObject->setData($data);

        $this->assertEquals($result, $dataObject->toArray());
    }

    public function testGet()
    {
        $key = 'key';
        $value = 'value';
        $data = [$key => $value];

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $dataObject = $objectManager->getObject('Magento\Framework\Data\Stub\DataObject');
        $dataObject->setData($data);

        $this->assertEquals($value, $dataObject->get($key));
    }
}
