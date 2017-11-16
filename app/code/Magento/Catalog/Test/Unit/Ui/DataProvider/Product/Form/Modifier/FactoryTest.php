<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Class FactoryTest
 */
class FactoryTest extends \PHPUnit\Framework\TestCase
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
     * @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var ModifierInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataProviderMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManagerHelper($this);
        $this->dataProviderMock = $this->getMockBuilder(ModifierInterface::class)
            ->getMockForAbstractClass();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            \Magento\Ui\DataProvider\Modifier\ModifierFactory::class,
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

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateWithException()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn(null);

        $this->model->create('');
    }
}
