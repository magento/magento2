<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\Model\ResourceModel;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Product;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Configurable|MockObject
     */
    private $configurableMock;

    /**
     * @var ActionInterface|MockObject
     */
    private $actionMock;

    /**
     * @var Product
     */
    private $model;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->configurableMock = $this->createMock(Configurable::class);
        $this->actionMock = $this->getMockForAbstractClass(ActionInterface::class);

        $this->model = $this->objectManager->getObject(
            Product::class,
            [
                'configurable' => $this->configurableMock,
                'productIndexer' => $this->actionMock,
            ]
        );
    }

    public function testBeforeSaveConfigurable()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product|MockObject $subject */
        $subject = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        /** @var \Magento\Catalog\Model\Product|MockObject $object */
        $object = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getTypeId', 'getTypeInstance']);
        $type = $this->createPartialMock(
            Configurable::class,
            ['getSetAttributes']
        );
        $type->expects($this->once())->method('getSetAttributes')->with($object);

        $object->expects($this->once())->method('getTypeId')->willReturn(Configurable::TYPE_CODE);
        $object->expects($this->once())->method('getTypeInstance')->willReturn($type);

        $this->model->beforeSave(
            $subject,
            $object
        );
    }

    public function testBeforeSaveSimple()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product|MockObject $subject */
        $subject = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        /** @var \Magento\Catalog\Model\Product|MockObject $object */
        $object = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getTypeId', 'getTypeInstance']);
        $object->expects($this->once())->method('getTypeId')->willReturn(Type::TYPE_SIMPLE);
        $object->expects($this->never())->method('getTypeInstance');

        $this->model->beforeSave(
            $subject,
            $object
        );
    }

    public function testAroundDelete()
    {
        $productId = '1';
        $parentConfigId = ['2'];
        /** @var \Magento\Catalog\Model\ResourceModel\Product|MockObject $subject */
        $subject = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        /** @var \Magento\Catalog\Model\Product|MockObject $product */
        $product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getId', 'delete']
        );
        $product->expects($this->once())->method('getId')->willReturn($productId);
        $product->expects($this->once())->method('delete')->willReturn(true);
        $this->configurableMock->expects($this->once())
            ->method('getParentIdsByChild')
            ->with($productId)
            ->willReturn($parentConfigId);
        $this->actionMock->expects($this->once())->method('executeList')->with($parentConfigId);

        $return = $this->model->aroundDelete(
            $subject,
            /** @var \Magento\Catalog\Model\Product|MockObject $prod */
            function (\Magento\Catalog\Model\Product $prod) use ($subject) {
                $prod->delete();
                return $subject;
            },
            $product
        );

        $this->assertEquals($subject->getTypeId(), $return->getTypeId());
    }
}
