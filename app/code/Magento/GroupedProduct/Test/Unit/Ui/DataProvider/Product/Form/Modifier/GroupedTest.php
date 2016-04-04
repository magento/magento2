<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\GroupedProduct\Ui\DataProvider\Product\Form\Modifier\Grouped;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProductType;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductLinkExtensionInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class GroupedTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupedTest extends AbstractModifierTest
{
    const PRODUCT_ID = 1;
    const LINKED_PRODUCT_ID = 2;
    const LINKED_PRODUCT_SKU = 'linked';
    const LINKED_PRODUCT_NAME = 'linked';
    const LINKED_PRODUCT_QTY = '0';
    const LINKED_PRODUCT_POSITION = 1;
    const LINKED_PRODUCT_PRICE = '1';

    /**
     * @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkedProductMock;

    /**
     * @var ProductLinkRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkRepositoryMock;

    /**
     * @var ProductLinkInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkMock;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var ProductLinkExtensionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkExtensionMock;

    /**
     * @var CurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyMock;

    /**
     * @var ImageHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageHelperMock;

    /**
     * @var AttributeSetRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeSetRepositoryMock;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;


    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->locatorMock = $this->getMockBuilder(LocatorInterface::class)
            ->getMockForAbstractClass();
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(['getId', 'getTypeId'])
            ->getMockForAbstractClass();
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::PRODUCT_ID);
        $this->productMock->expects($this->any())
            ->method('getTypeId')
            ->willReturn(GroupedProductType::TYPE_CODE);
        $this->linkedProductMock = $this->getMockBuilder(ProductInterface::class)
            ->setMethods(['getId', 'getName', 'getPrice'])
            ->getMockForAbstractClass();
        $this->linkedProductMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::LINKED_PRODUCT_ID);
        $this->linkedProductMock->expects($this->any())
            ->method('getName')
            ->willReturn(self::LINKED_PRODUCT_NAME);
        $this->linkedProductMock->expects($this->any())
            ->method('getPrice')
            ->willReturn(self::LINKED_PRODUCT_PRICE);
        $this->linkMock = $this->getMockBuilder(ProductLinkInterface::class)
            ->setMethods(['getLinkType', 'getLinkedProductSku', 'getPosition', 'getExtensionAttributes'])
            ->getMockForAbstractClass();
        $this->linkExtensionMock = $this->getMockBuilder(ProductLinkExtensionInterface::class)
            ->setMethods(['getQty'])
            ->getMockForAbstractClass();
        $this->linkExtensionMock->expects($this->any())
            ->method('getQty')
            ->willReturn(self::LINKED_PRODUCT_QTY);
        $this->linkMock->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($this->linkExtensionMock);
        $this->linkMock->expects($this->any())
            ->method('getPosition')
            ->willReturn(self::LINKED_PRODUCT_POSITION);
        $this->linkMock->expects($this->any())
            ->method('getLinkedProductSku')
            ->willReturn(self::LINKED_PRODUCT_SKU);
        $this->linkMock->expects($this->any())
            ->method('getLinkType')
            ->willReturn(Grouped::LINK_TYPE);
        $this->linkRepositoryMock = $this->getMockBuilder(ProductLinkRepositoryInterface::class)
            ->setMethods(['getList'])
            ->getMockForAbstractClass();
        $this->linkRepositoryMock->expects($this->any())
            ->method('getList')
            ->with($this->productMock)
            ->willReturn([$this->linkMock]);
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->productRepositoryMock->expects($this->any())
            ->method('get')
            ->with(self::LINKED_PRODUCT_SKU)
            ->willReturn($this->linkedProductMock);
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->locatorMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->locatorMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        $this->currencyMock = $this->getMockBuilder(CurrencyInterface::class)
            ->setMethods(['getCurrency', 'toCurrency'])
            ->getMockForAbstractClass();
        $this->currencyMock->expects($this->any())
            ->method('getCurrency')
            ->willReturn($this->currencyMock);
        $this->imageHelperMock = $this->getMockBuilder(ImageHelper::class)
            ->setMethods(['init', 'getUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageHelperMock->expects($this->any())
            ->method('init')
            ->willReturn($this->imageHelperMock);
        $this->attributeSetRepositoryMock = $this->getMockBuilder(AttributeSetRepositoryInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $attributeSetMock = $this->getMockBuilder(AttributeSetInterface::class)
            ->setMethods(['getAttributeSetName'])
            ->getMockForAbstractClass();
        $this->attributeSetRepositoryMock->expects($this->any())
            ->method('get')
            ->willReturn($attributeSetMock);

        return $this->objectManager->getObject(Grouped::class, [
            'locator' => $this->locatorMock,
            'productLinkRepository' => $this->linkRepositoryMock,
            'productRepository' => $this->productRepositoryMock,
            'localeCurrency' => $this->currencyMock,
            'imageHelper' => $this->imageHelperMock,
            'attributeSetRepository' => $this->attributeSetRepositoryMock,
        ]);
    }

    public function testModifyMeta()
    {
        $this->assertArrayHasKey(Grouped::GROUP_GROUPED, $this->getModel()->modifyMeta([]));
    }

    /**
     * {@inheritdoc}
     */
    public function testModifyData()
    {
        $expectedData = [
            self::PRODUCT_ID => [
                'links' => [
                    Grouped::LINK_TYPE => [
                        [
                            'id' => self::LINKED_PRODUCT_ID,
                            'name' => self::LINKED_PRODUCT_NAME,
                            'sku' => self::LINKED_PRODUCT_SKU,
                            'price' => null,
                            'qty' => self::LINKED_PRODUCT_QTY,
                            'position' => self::LINKED_PRODUCT_POSITION,
                            'thumbnail' => null,
                            'type_id' => null,
                            'status' => null,
                            'attribute_set' => null
                        ],
                    ],
                ],
                'product' => [
                    'current_store_id' => null
                ],
            ],
        ];
        $this->assertSame($expectedData, $this->getModel()->modifyData([]));
    }
}
