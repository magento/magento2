<?php
/**
 * Unit test for Magento\Framework\ValidatorFactory
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ValidatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Framework\ValidatorFactory */
    private $model;

    /** @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $objectManagerMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->model = $objectManager->getObject(
            \Magento\Framework\ValidatorFactory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreateWithInstanceName()
    {
        $setName = \Magento\Framework\DataObject::class;
        $returnMock = $this->getMock($setName);
        $this->objectManagerMock->expects($this->once())->method('create')
            ->willReturn($returnMock);

        $this->assertSame($returnMock, $this->model->create());
    }

    public function testCreateDefault()
    {
        $default = \Magento\Framework\Validator::class;
        $returnMock = $this->getMock($default);
        $this->objectManagerMock->expects($this->once())->method('create')
            ->willReturn($returnMock);
        $this->assertSame($returnMock, $this->model->create());
    }
}
