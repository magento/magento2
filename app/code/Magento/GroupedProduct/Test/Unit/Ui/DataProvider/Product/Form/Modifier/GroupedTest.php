<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkExtensionInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTest;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GroupedProduct\Model\Product\Link\CollectionProvider\Grouped as GroupedProducts;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProductType;
use Magento\GroupedProduct\Ui\DataProvider\Product\Form\Modifier\Grouped;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupedTest extends AbstractModifierTest
{
    private const PRODUCT_ID = 1;
    private const LINKED_PRODUCT_ID = 2;
    private const LINKED_PRODUCT_SKU = 'linked';
    private const LINKED_PRODUCT_NAME = 'linked';
    private const LINKED_PRODUCT_QTY = '0';
    private const LINKED_PRODUCT_POSITION = 1;
    private const LINKED_PRODUCT_POSITION_CALCULATED = 1;
    private const LINKED_PRODUCT_PRICE = '1';

    /**
     * @var ProductInterface|MockObject
     */
    protected $linkedProductMock;

    /**
     * @var ProductLinkRepositoryInterface|MockObject
     */
    protected $linkRepositoryMock;

    /**
     * @var ProductLinkInterface|MockObject
     */
    protected $linkMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var ProductLinkExtensionInterface|MockObject
     */
    protected $linkExtensionMock;

    /**
     * @var CurrencyInterface|MockObject
     */
    protected $currencyMock;

    /**
     * @var ImageHelper|MockObject
     */
    protected $imageHelperMock;

    /**
     * @var AttributeSetRepositoryInterface|MockObject
     */
    protected $attributeSetRepositoryMock;

    /**
     * @var StoreInterface|MockObject
     */
    protected $storeMock;

    /**
     * @var GroupedProducts|MockObject
     */
    private $groupedProductsMock;

    /**
     * @var ProductLinkInterfaceFactory|MockObject
     */
    private $productLinkFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->locatorMock = $this->getMockBuilder(LocatorInterface::class)
            ->getMockForAbstractClass();
        $this->productMock = $this->getMockBuilder(Product::class)
            ->onlyMethods(['getId', 'getTypeId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::PRODUCT_ID);
        $this->productMock->expects($this->any())
            ->method('getTypeId')
            ->willReturn(GroupedProductType::TYPE_CODE);
        $this->linkedProductMock = $this->getMockBuilder(Product::class)
            ->onlyMethods(['getId', 'getName', 'getPrice'])
            ->disableOriginalConstructor()
            ->getMock();
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
            ->onlyMethods(['getLinkType', 'getLinkedProductSku', 'getPosition', 'getExtensionAttributes'])
            ->getMockForAbstractClass();
        $this->linkExtensionMock = $this->getMockBuilder(ProductLinkExtensionInterface::class)
            ->addMethods(['getQty'])
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
            ->onlyMethods(['getList'])
            ->getMockForAbstractClass();
        $this->linkRepositoryMock->expects($this->any())
            ->method('getList')
            ->with($this->productMock)
            ->willReturn([$this->linkedProductMock]);
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->onlyMethods(['get'])
            ->getMockForAbstractClass();
        $this->productRepositoryMock->expects($this->any())
            ->method('get')
            ->with(self::LINKED_PRODUCT_SKU)
            ->willReturn($this->linkedProductMock);
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();
        $this->locatorMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->locatorMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
    }

    /**
     * @inheritdoc
     */
    protected function createModel()
    {
        $this->currencyMock = $this->getMockBuilder(CurrencyInterface::class)
            ->addMethods(['toCurrency'])
            ->onlyMethods(['getCurrency'])
            ->getMockForAbstractClass();
        $this->currencyMock->expects($this->any())
            ->method('getCurrency')
            ->willReturn($this->currencyMock);
        $this->imageHelperMock = $this->getMockBuilder(ImageHelper::class)
            ->onlyMethods(['init', 'getUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->groupedProductsMock = $this->getMockBuilder(GroupedProducts::class)
            ->onlyMethods(['getLinkedProducts'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productLinkFactoryMock = $this->getMockBuilder(ProductLinkInterfaceFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->imageHelperMock->expects($this->any())
            ->method('init')
            ->willReturn($this->imageHelperMock);
        $this->attributeSetRepositoryMock = $this->getMockBuilder(AttributeSetRepositoryInterface::class)
            ->onlyMethods(['get'])
            ->getMockForAbstractClass();
        $attributeSetMock = $this->getMockBuilder(AttributeSetInterface::class)
            ->onlyMethods(['getAttributeSetName'])
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
            'groupedProducts' => $this->groupedProductsMock,
            'productLinkFactory' => $this->productLinkFactoryMock,
        ]);
    }

    /**
     * Assert array has key
     *
     * @return void
     */
    public function testModifyMeta()
    {
        $this->assertArrayHasKey(Grouped::GROUP_GROUPED, $this->getModel()->modifyMeta([]));
    }

    /**
     * @inheritdoc
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
                            'positionCalculated' => self::LINKED_PRODUCT_POSITION_CALCULATED,
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
        $model = $this->getModel();
        $linkedProductMock = $this->getMockBuilder(Product::class)
            ->addMethods(['getPosition'])
            ->onlyMethods(['getId', 'getName', 'getPrice', 'getSku', 'getImage', 'getQty'])
            ->disableOriginalConstructor()
            ->getMock();
        $linkedProductMock->expects($this->once())
            ->method('getId')
            ->willReturn(self::LINKED_PRODUCT_ID);
        $linkedProductMock->expects($this->once())
            ->method('getName')
            ->willReturn(self::LINKED_PRODUCT_NAME);
        $linkedProductMock->expects($this->once())
            ->method('getPrice')
            ->willReturn(self::LINKED_PRODUCT_PRICE);
        $linkedProductMock->expects($this->once())
            ->method('getSku')
            ->willReturn(self::LINKED_PRODUCT_SKU);
        $linkedProductMock->expects($this->once())
            ->method('getImage')
            ->willReturn('');
        $linkedProductMock->expects($this->exactly(2))
            ->method('getPosition')
            ->willReturn(self::LINKED_PRODUCT_POSITION);
        $linkedProductMock->expects($this->once())
            ->method('getQty')
            ->willReturn(self::LINKED_PRODUCT_QTY);
        $this->groupedProductsMock->expects($this->once())
            ->method('getLinkedProducts')
            ->willReturn([$linkedProductMock]);
        $linkMock = $this->getMockBuilder(ProductLinkInterface::class)
            ->getMockForAbstractClass();

        $this->productLinkFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($linkMock);

        $this->assertSame($expectedData, $model->modifyData([]));
    }
}
