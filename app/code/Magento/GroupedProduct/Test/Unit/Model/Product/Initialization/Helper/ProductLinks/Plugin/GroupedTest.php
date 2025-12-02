<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Model\Product\Initialization\Helper\ProductLinks\Plugin;

use Magento\Catalog\Test\Unit\Helper\ProductTestHelper;
use Magento\Catalog\Test\Unit\Helper\ProductLinkExtensionInterfaceTestHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Api\Data\ProductLinkExtensionFactory;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks;
use Magento\Catalog\Model\Product\Type;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupedTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $productLinkExtensionFactory;

    /**
     * @var MockObject
     */
    protected $productLinkFactory;

    /**
     * @var MockObject
     */
    protected $productRepository;

    /**
     * @var \Magento\GroupedProduct\Model\Product\Initialization\Helper\ProductLinks\Plugin\Grouped
     */
    protected $model;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var ProductLinks|MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->productMock = $this->createPartialMock(
            ProductTestHelper::class,
            ['__wakeup', 'getTypeId', 'getSku', 'getProductLinks', 'setProductLinks',
                'getGroupedReadonly', 'setGroupedLinkData']
        );
        $this->subjectMock = $this->createMock(
            ProductLinks::class
        );
        $this->productLinkExtensionFactory = $this->createMock(ProductLinkExtensionFactory::class);
        $this->productLinkFactory = $this->createMock(ProductLinkInterfaceFactory::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->model = new \Magento\GroupedProduct\Model\Product\Initialization\Helper\ProductLinks\Plugin\Grouped(
            $this->productLinkFactory,
            $this->productRepository,
            $this->productLinkExtensionFactory
        );
    }

    #[DataProvider('productTypeDataProvider')]
    public function testBeforeInitializeLinksRequestDoesNotHaveGrouped($productType)
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn($productType);
        $this->productMock->expects($this->never())->method('getGroupedReadonly');
        $this->productMock->expects($this->never())->method('setGroupedLinkData');
        $this->model->beforeInitializeLinks($this->subjectMock, $this->productMock, []);
    }

    /**
     * @return array
     */
    public static function productTypeDataProvider()
    {
        return [
            [Type::TYPE_SIMPLE],
            [Type::TYPE_BUNDLE],
            [Type::TYPE_VIRTUAL]
        ];
    }

    #[DataProvider('linksDataProvider')]
    public function testBeforeInitializeLinksRequestHasGrouped($linksData)
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn(Grouped::TYPE_CODE);
        $this->productMock->expects($this->once())->method('getGroupedReadonly')->willReturn(false);
        $this->productMock->expects($this->once())->method('setProductLinks')
            ->with($this->arrayHasKey(0));
        $this->productMock->expects($this->once())->method('getProductLinks')->willReturn([]);
        $this->productMock->expects($this->once())->method('getSku')->willReturn('sku');
        $linkedProduct = $this->createPartialMock(
            ProductTestHelper::class,
            ['__wakeup', 'getTypeId', 'getSku', 'getProductLinks', 'setProductLinks', 'getGroupedReadonly']
        );
        $extensionAttributes = new ProductLinkExtensionInterfaceTestHelper();
        $linkedProduct->expects($this->once())->method('getTypeId')->willReturn(Grouped::TYPE_CODE);
        $linkedProduct->expects($this->once())->method('getSku')->willReturn('sku');
        $productLink = $this->createMock(ProductLinkInterface::class);
        $this->productRepository->expects($this->once())
            ->method('getById')
            ->willReturn($linkedProduct);
        $this->productLinkFactory->expects($this->once())->method('create')->willReturn($productLink);
        $productLink->expects($this->once())->method('setSku')->with('sku')->willReturnSelf();
        $productLink->expects($this->once())->method('setLinkType')
            ->with('associated')->willReturnSelf();
        $productLink->expects($this->once())->method('setLinkedProductSku')
            ->with('sku')->willReturnSelf();
        $productLink->expects($this->once())->method('setLinkedProductType')
            ->with(Grouped::TYPE_CODE)
            ->willReturnSelf();
        $productLink->expects($this->once())->method('setPosition')->willReturnSelf();
        $productLink->expects($this->once())->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $extensionAttributes->setQty(1);
        $this->model->beforeInitializeLinks($this->subjectMock, $this->productMock, ['associated' => $linksData]);
    }

    /**
     * @return array
     */
    public static function linksDataProvider()
    {
        return [
            [[5 => ['id' => '2', 'qty' => '100', 'position' => '1']]]
        ];
    }

    public function testBeforeInitializeLinksProductIsReadonly()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn(Grouped::TYPE_CODE);
        $this->productMock->expects($this->once())->method('getGroupedReadonly')->willReturn(true);
        $this->productMock->expects($this->never())->method('setGroupedLinkData');
        $this->model->beforeInitializeLinks($this->subjectMock, $this->productMock, ['associated' => 'value']);
    }
}
