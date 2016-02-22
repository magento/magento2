<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit\Entity;

use Magento\Framework\Model\Entity\EntityHydrator;

/**
 * Class EntityHydratorTest
 */
class EntityHydratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityHydrator
     */
    protected $entityHydrator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectMock;

    protected function setUp()
    {
        $this->dataObjectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityHydrator = new EntityHydrator();
    }

    public function testExtract()
    {
        $data = ['id' => 1, 'description' => 'some description'];
        $this->dataObjectMock->expects($this->once())->method('getData')->willReturn($data);
        $this->entityHydrator->extract($this->dataObjectMock);
    }

    public function testHydrate()
    {
        $data = ['id' => 1, 'description' => 'some description'];
        $dataToMerge = ['qty' => 2];
        $this->dataObjectMock->expects($this->once())->method('getData')->willReturn($data);
        $this->dataObjectMock->expects($this->once())->method('setData')->with(array_merge($data, $dataToMerge));
        $this->assertEquals(
            $this->dataObjectMock,
            $this->entityHydrator->hydrate($this->dataObjectMock, $dataToMerge)
        );
    }
}
