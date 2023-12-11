<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\Composite;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompositeTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Composite
     */
    protected $composite;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var LocatorInterface|MockObject
     */
    protected $locatorMock;

    /**
     * @var ProductInterface|MockObject
     */
    protected $productMock;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @var array
     */
    protected $modifiedMeta = [];

    /**
     * @var string
     */
    protected $modifierClass;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->meta = ['some_meta'];
        $this->modifiedMeta = ['modified_meta'];
        $this->modifierClass = 'SomeClass';
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->locatorMock = $this->getMockForAbstractClass(LocatorInterface::class);
        $this->productMock = $this->getMockForAbstractClass(ProductInterface::class);

        $this->locatorMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $this->composite = $this->objectManagerHelper->getObject(
            Composite::class,
            [
                'locator' => $this->locatorMock,
                'objectManager' => $this->objectManagerMock,
                'modifiers' => ['mod' => $this->modifierClass]
            ]
        );
    }

    /**
     * @return void
     */
    public function testModifyMetaWithoutModifiers()
    {
        $this->composite = $this->objectManagerHelper->getObject(
            Composite::class,
            [
                'locator' => $this->locatorMock,
                'objectManager' => $this->objectManagerMock,
                'modifiers' => []
            ]
        );
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_CODE);
        $this->objectManagerMock->expects($this->never())
            ->method('get');

        $this->assertSame($this->meta, $this->composite->modifyMeta($this->meta));
    }

    /**
     * @return void
     */
    public function testModifyMetaBundleProduct()
    {
        /** @var ModifierInterface|MockObject $modifierMock */
        $modifierMock = $this->getMockForAbstractClass(ModifierInterface::class);
        $modifierMock->expects($this->once())
            ->method('modifyMeta')
            ->with($this->meta)
            ->willReturn($this->modifiedMeta);

        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_CODE);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->modifierClass)
            ->willReturn($modifierMock);

        $this->assertSame($this->modifiedMeta, $this->composite->modifyMeta($this->meta));
    }

    /**
     * @return void
     */
    public function testModifyMetaNonBundleProduct()
    {
        /** @var ModifierInterface|MockObject $modifierMock */
        $modifierMock = $this->getMockForAbstractClass(ModifierInterface::class);
        $modifierMock->expects($this->never())
            ->method('modifyMeta');

        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('SomeTypeProduct');
        $this->objectManagerMock->expects($this->never())
            ->method('get');

        $this->assertSame($this->meta, $this->composite->modifyMeta($this->meta));
    }

    /**
     * @return void
     */
    public function testModifyMetaWithException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Type "SomeClass" is not an instance of Magento\\Ui\\DataProvider\\Modifier\\ModifierInterface'
        );

        /** @var \Exception|MockObject $modifierMock */
        $modifierMock = $this->getMockBuilder(\Exception::class)->addMethods(['modifyMeta'])
            ->disableOriginalConstructor()
            ->getMock();
        $modifierMock->expects($this->never())
            ->method('modifyMeta');

        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_CODE);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($this->modifierClass)
            ->willReturn($modifierMock);

        $this->composite->modifyMeta($this->meta);
    }
}
