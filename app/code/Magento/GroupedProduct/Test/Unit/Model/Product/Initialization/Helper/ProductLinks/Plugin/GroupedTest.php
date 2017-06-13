<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Model\Product\Initialization\Helper\ProductLinks\Plugin;

use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Catalog\Model\Product\Type;

/**
 * Class GroupedTest
 */
class GroupedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLinkExtensionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLinkFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

    /**
     * @var \Magento\GroupedProduct\Model\Product\Initialization\Helper\ProductLinks\Plugin\Grouped
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->productMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getGroupedReadonly', '__wakeup', 'getTypeId', 'getSku', 'getProductLinks', 'setProductLinks'],
            [],
            '',
            false
        );
        $this->subjectMock = $this->getMock(
            \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks::class,
            [],
            [],
            '',
            false
        );
        $this->productLinkExtensionFactory = $this->getMockBuilder(
            \Magento\Catalog\Api\Data\ProductLinkExtensionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->productLinkFactory = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductLinkInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->model = new \Magento\GroupedProduct\Model\Product\Initialization\Helper\ProductLinks\Plugin\Grouped(
            $this->productLinkFactory,
            $this->productRepository,
            $this->productLinkExtensionFactory
        );
    }

    /**
     * @dataProvider productTypeDataProvider
     */
    public function testBeforeInitializeLinksRequestDoesNotHaveGrouped($productType)
    {
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue($productType));
        $this->productMock->expects($this->never())->method('getGroupedReadonly');
        $this->productMock->expects($this->never())->method('setGroupedLinkData');
        $this->model->beforeInitializeLinks($this->subjectMock, $this->productMock, []);
    }

    public function productTypeDataProvider()
    {
        return [
            [Type::TYPE_SIMPLE],
            [Type::TYPE_BUNDLE],
            [Type::TYPE_VIRTUAL]
        ];
    }

    /**
     * @dataProvider linksDataProvider
     */
    public function testBeforeInitializeLinksRequestHasGrouped($linksData)
    {
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(Grouped::TYPE_CODE));
        $this->productMock->expects($this->once())->method('getGroupedReadonly')->will($this->returnValue(false));
        $this->productMock->expects($this->once())->method('setProductLinks')->with($this->arrayHasKey(0));
        $this->productMock->expects($this->once())->method('getProductLinks')->willReturn([]);
        $this->productMock->expects($this->once())->method('getSku')->willReturn('sku');
        $linkedProduct = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getGroupedReadonly', '__wakeup', 'getTypeId', 'getSku', 'getProductLinks', 'setProductLinks'],
            [],
            '',
            false
        );
        $extensionAttributes = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductLinkExtensionInterface::class)
            ->setMethods(['setQty', 'getQty'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $linkedProduct->expects($this->once())->method('getTypeId')->will($this->returnValue(Grouped::TYPE_CODE));
        $linkedProduct->expects($this->once())->method('getSku')->willReturn('sku');
        $productLink = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductLinkInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productRepository->expects($this->once())
            ->method('getById')
            ->willReturn($linkedProduct);
        $this->productLinkFactory->expects($this->once())->method('create')->willReturn($productLink);
        $productLink->expects($this->once())->method('setSku')->with('sku')->willReturnSelf();
        $productLink->expects($this->once())->method('setLinkType')->with('associated')->willReturnSelf();
        $productLink->expects($this->once())->method('setLinkedProductSku')->with('sku')->willReturnSelf();
        $productLink->expects($this->once())->method('setLinkedProductType')
            ->with(Grouped::TYPE_CODE)
            ->willReturnSelf();
        $productLink->expects($this->once())->method('setPosition')->willReturnSelf();
        $productLink->expects($this->once())->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $extensionAttributes->expects($this->once())->method('setQty')->willReturnSelf();
        $this->model->beforeInitializeLinks($this->subjectMock, $this->productMock, ['associated' => $linksData]);
    }

    public function linksDataProvider()
    {
        return [
            [[5 => ['id' => '2', 'qty' => '100', 'position' => '1']]]
        ];
    }

    public function testBeforeInitializeLinksProductIsReadonly()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(Grouped::TYPE_CODE));
        $this->productMock->expects($this->once())->method('getGroupedReadonly')->will($this->returnValue(true));
        $this->productMock->expects($this->never())->method('setGroupedLinkData');
        $this->model->beforeInitializeLinks($this->subjectMock, $this->productMock, ['associated' => 'value']);
    }
}
