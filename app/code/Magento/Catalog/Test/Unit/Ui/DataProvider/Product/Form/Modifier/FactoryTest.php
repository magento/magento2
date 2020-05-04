<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Ui\DataProvider\Modifier\ModifierFactory;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var ModifierFactory
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManager;

    /**
     * @var ObjectManager|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var ModifierInterface|MockObject
     */
    protected $dataProviderMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManagerHelper($this);
        $this->dataProviderMock = $this->getMockBuilder(ModifierInterface::class)
            ->getMockForAbstractClass();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            ModifierFactory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreate()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($this->dataProviderMock);

        $this->assertInstanceOf(ModifierInterface::class, $this->model->create(ModifierInterface::class));
    }

    public function testCreateWithException()
    {
        $this->expectException('InvalidArgumentException');
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn(null);

        $this->model->create('');
    }
}
