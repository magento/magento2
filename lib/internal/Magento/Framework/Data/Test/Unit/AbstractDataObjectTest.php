<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit;

use Magento\Framework\Data\Test\Unit\Stub\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class AbstractDataObjectTest extends TestCase
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

        $objectManager = new ObjectManager($this);

        $subObject = $objectManager->getObject(DataObject::class);
        $subObject->setData($subObjectData);

        $nestedObject = $objectManager->getObject(DataObject::class);
        $nestedObject->setData($nestedObjectData);

        $dataObject = $objectManager->getObject(DataObject::class);
        $data = ['key' => 'value', 'object' => $subObject, 'nestedArray' => ['nestedObject' => $nestedObject]];
        $dataObject->setData($data);

        $this->assertEquals($result, $dataObject->toArray());
    }

    public function testGet()
    {
        $key = 'key';
        $value = 'value';
        $data = [$key => $value];

        $objectManager = new ObjectManager($this);
        $dataObject = $objectManager->getObject(DataObject::class);
        $dataObject->setData($data);

        $this->assertEquals($value, $dataObject->get($key));
    }
}
