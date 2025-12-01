<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTestCase;
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
class GroupedTest extends AbstractModifierTestCase
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
        $this->locatorMock = $this->createMock(LocatorInterface::class);
        $this->productMock = $this->createPartialMock(Product::class, ['getId', 'getTypeId']);
        $this->productMock->method('getId')->willReturn(self::PRODUCT_ID);
        $this->productMock->method('getTypeId')->willReturn(GroupedProductType::TYPE_CODE);
        $this->linkedProductMock = $this->createPartialMock(Product::class, ['getId', 'getName', 'getPrice']);
        $this->linkedProductMock->method('getId')->willReturn(self::LINKED_PRODUCT_ID);
        $this->linkedProductMock->method('getName')->willReturn(self::LINKED_PRODUCT_NAME);
        $this->linkedProductMock->method('getPrice')->willReturn(self::LINKED_PRODUCT_PRICE);
        $this->linkMock = $this->createMock(ProductLinkInterface::class);
        $this->linkExtensionMock = new \Magento\Catalog\Test\Unit\Helper\ProductLinkExtensionInterfaceTestHelper();
        $this->linkExtensionMock->setQty(self::LINKED_PRODUCT_QTY);
        $this->linkMock->method('getExtensionAttributes')->willReturn($this->linkExtensionMock);
        $this->linkMock->method('getPosition')->willReturn(self::LINKED_PRODUCT_POSITION);
        $this->linkMock->method('getLinkedProductSku')->willReturn(self::LINKED_PRODUCT_SKU);
        $this->linkMock->method('getLinkType')->willReturn(Grouped::LINK_TYPE);
        $this->linkRepositoryMock = $this->createMock(ProductLinkRepositoryInterface::class);
        $this->linkRepositoryMock->expects($this->any())
            ->method('getList')
            ->with($this->productMock)
            ->willReturn([$this->linkedProductMock]);
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->productRepositoryMock->expects($this->any())
            ->method('get')
            ->with(self::LINKED_PRODUCT_SKU)
            ->willReturn($this->linkedProductMock);
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->locatorMock->method('getProduct')->willReturn($this->productMock);
        $this->locatorMock->method('getStore')->willReturn($this->storeMock);
    }

    /**
     * @inheritdoc
     */
    protected function createModel()
    {
        $this->currencyMock = new \Magento\Framework\Locale\Test\Unit\Helper\CurrencyTestHelper();
        $this->imageHelperMock = $this->createPartialMock(ImageHelper::class, ['init', 'getUrl']);

        $this->groupedProductsMock = $this->createPartialMock(GroupedProducts::class, ['getLinkedProducts']);
        $this->productLinkFactoryMock = $this->createMock(ProductLinkInterfaceFactory::class);

        $this->imageHelperMock->method('init')->willReturn($this->imageHelperMock);
        $this->attributeSetRepositoryMock = $this->createMock(AttributeSetRepositoryInterface::class);
        $attributeSetMock = $this->createMock(AttributeSetInterface::class);
        $this->attributeSetRepositoryMock->method('get')->willReturn($attributeSetMock);

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
                            'price' => '$1.00',
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
        $linkedProductMock = $this->createPartialMock(
            \Magento\Catalog\Test\Unit\Helper\ProductTestHelper::class,
            ['getId', 'getName', 'getPrice', 'getSku', 'getImage', 'getQty', 'getPosition']
        );
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
        $linkMock = $this->createMock(ProductLinkInterface::class);

        $this->productLinkFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($linkMock);

        $this->assertSame($expectedData, $model->modifyData([]));
    }
}
