<?php
/**
 * Unit test for Magento\Framework\ValidatorFactory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ValidatorFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \Magento\Framework\ValidatorFactory */
    private $model;

    /** @var \Magento\Framework\ObjectManagerInterface | \PHPUnit\Framework\MockObject\MockObject */
    private $objectManagerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->model = $objectManager->getObject(
            \Magento\Framework\ValidatorFactory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreateWithInstanceName()
    {
        $setName = \Magento\Framework\DataObject::class;
        $returnMock = $this->createMock($setName);
        $this->objectManagerMock->expects($this->once())->method('create')
            ->willReturn($returnMock);

        $this->assertSame($returnMock, $this->model->create());
    }

    public function testCreateDefault()
    {
        $default = \Magento\Framework\Validator::class;
        $returnMock = $this->createMock($default);
        $this->objectManagerMock->expects($this->once())->method('create')
            ->willReturn($returnMock);
        $this->assertSame($returnMock, $this->model->create());
    }
}
