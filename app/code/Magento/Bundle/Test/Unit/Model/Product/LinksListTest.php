<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Product;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Model\Product\LinksList;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\DataObjectHelper;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Test\Unit\Helper\ProductTestHelper;

class LinksListTest extends TestCase
{
    /**
     * @var LinksList
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $linkFactoryMock;

    /**
     * @var ProductTestHelper
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $productTypeMock;

    /**
     * @var ProductTestHelper
     */
    protected $selectionMock;

    /**
     * @var MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->linkFactoryMock = $this->createPartialMock(
            LinkInterfaceFactory::class,
            [
                'create',
            ]
        );
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->selectionMock = new ProductTestHelper();
        $this->productMock = new ProductTestHelper();
        $this->productTypeMock = $this->createMock(Type::class);
        $this->model = new LinksList($this->linkFactoryMock, $this->productTypeMock, $this->dataObjectHelperMock);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testLinksList()
    {
        $optionId = 665;
        $selectionId = 1345;
        $this->productTypeMock->expects($this->once())
            ->method('getSelectionsCollection')
            ->with([$optionId], $this->productMock)
            ->willReturn([$this->selectionMock]);
        $this->productMock->setPriceType('price_type');
        $this->selectionMock->setSelectionPriceType('selection_price_type');
        $this->selectionMock->setSelectionPriceValue(12);
        $this->selectionMock->setData([0 => 'some data']);
        $this->selectionMock->setSelectionId($selectionId);
        $this->selectionMock->setIsDefault(true);
        $this->selectionMock->setSelectionQty(66);
        $this->selectionMock->setSelectionCanChangeQty(22);
        $linkMock = $this->createMock(LinkInterface::class);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($linkMock, ['some data'], LinkInterface::class)->willReturnSelf();
        $linkMock->expects($this->once())->method('setIsDefault')->with(true)->willReturnSelf();
        $linkMock->expects($this->once())->method('setQty')->with(66)->willReturnSelf();
        $linkMock->expects($this->once())->method('setCanChangeQuantity')->with(22)->willReturnSelf();
        $linkMock->expects($this->once())->method('setPrice')->with(12)->willReturnSelf();
        $linkMock->expects($this->once())->method('setId')->with($selectionId)->willReturnSelf();
        $linkMock->expects($this->once())
            ->method('setPriceType')->with('selection_price_type')->willReturnSelf();
        $this->linkFactoryMock->expects($this->once())->method('create')->willReturn($linkMock);

        $this->assertEquals([$linkMock], $this->model->getItems($this->productMock, $optionId));
    }
}
