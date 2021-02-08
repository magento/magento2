<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\Model\ResourceModel;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Indexer\ActionInterface;

class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var Configurable|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configurableMock;

    /**
     * @var ActionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $actionMock;

    /**
     * @var \Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Product
     */
    private $model;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->configurableMock = $this->createMock(Configurable::class);
        $this->actionMock = $this->getMockForAbstractClass(ActionInterface::class);

        $this->model = $this->objectManager->getObject(
            \Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Product::class,
            [
                'configurable' => $this->configurableMock,
                'productIndexer' => $this->actionMock,
            ]
        );
    }

    public function testBeforeSaveConfigurable()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product|\PHPUnit\Framework\MockObject\MockObject $subject */
        $subject = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        /** @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject $object */
        $object = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getTypeId', 'getTypeInstance']);
        $type = $this->createPartialMock(
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::class,
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
        /** @var \Magento\Catalog\Model\ResourceModel\Product|\PHPUnit\Framework\MockObject\MockObject $subject */
        $subject = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        /** @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject $object */
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
        /** @var \Magento\Catalog\Model\ResourceModel\Product|\PHPUnit\Framework\MockObject\MockObject $subject */
        $subject = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        /** @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject $product */
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
            /** @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject $prod */
            function (\Magento\Catalog\Model\Product $prod) use ($subject) {
                $prod->delete();
                return $subject;
            },
            $product
        );

        $this->assertEquals($subject->getTypeId(), $return->getTypeId());
    }
}
