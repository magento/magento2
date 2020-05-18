<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model;

use Exception;
use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Model\LinkManagement;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\ResourceModel\Bundle;
use Magento\Bundle\Model\ResourceModel\BundleFactory;
use Magento\Bundle\Model\ResourceModel\Option\Collection as OptionCollection;
use Magento\Bundle\Model\ResourceModel\Option\CollectionFactory as OptionCollectionFactory;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as SelectionCollection;
use Magento\Bundle\Model\Selection;
use Magento\Bundle\Model\SelectionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Bundle\Model\LinkManagement
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkManagementTest extends TestCase
{
    /**
     * @var LinkManagement
     */
    private $model;

    /**
     * @var ProductRepository|MockObject
     */
    private $productRepository;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var LinkInterfaceFactory|MockObject
     */
    private $linkFactory;

    /**
     * @var Type|MockObject
     */
    private $productType;

    /**
     * @var OptionCollection|MockObject
     */
    private $optionCollection;

    /**
     * @var SelectionCollection|MockObject
     */
    private $selectionCollection;

    /**
     * @var Option|MockObject
     */
    private $option;

    /**
     * @var SelectionFactory|MockObject
     */
    private $bundleSelectionMock;

    /**
     * @var BundleFactory|MockObject
     */
    private $bundleFactoryMock;

    /**
     * @var OptionCollectionFactory|MockObject
     */
    private $optionCollectionFactoryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var LinkInterface|MockObject
     */
    private $link;

    /**
     * @var MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    /**
     * @var EntityMetadata|MockObject
     */
    private $metadataMock;

    /**
     * @var int
     */
    private $storeId = 2;

    /**
     * @var array
     */
    private $optionIds = [1, 2, 3];

    /**
     * @var string
     */
    private $linkField = 'product_id';

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->productRepository = $this->getMockBuilder(ProductRepository::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productType = $this->getMockBuilder(\Magento\Bundle\Model\Product\Type::class)
            ->setMethods(['getOptionsCollection', 'setStoreFilter', 'getSelectionsCollection', 'getOptionsIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->option = $this->getMockBuilder(Option::class)
            ->setMethods(['getSelections', 'getOptionId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionCollection = $this->getMockBuilder(OptionCollection::class)
            ->setMethods(['appendSelections'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectionCollection = $this->getMockBuilder(
            SelectionCollection::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->product = $this->getMockBuilder(Product::class)
            ->setMethods(['getTypeInstance', 'getStoreId', 'getTypeId', '__wakeup', 'getId', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->link = $this->getMockBuilder(LinkInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->linkFactory = $this->getMockBuilder(LinkInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->bundleSelectionMock = $this->createPartialMock(
            SelectionFactory::class,
            ['create']
        );
        $this->bundleFactoryMock = $this->createPartialMock(
            BundleFactory::class,
            ['create']
        );
        $this->optionCollectionFactoryMock = $this->createPartialMock(
            OptionCollectionFactory::class,
            ['create']
        );
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataMock = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($this->metadataMock);

        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $helper->getObject(
            LinkManagement::class,
            [
                'productRepository' => $this->productRepository,
                'linkFactory' => $this->linkFactory,
                'bundleFactory' => $this->bundleFactoryMock,
                'bundleSelection' => $this->bundleSelectionMock,
                'optionCollection' => $this->optionCollectionFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'metadataPool' => $this->metadataPoolMock
            ]
        );
    }

    public function testGetChildren()
    {
        $productSku = 'productSku';

        $this->getOptions();

        $this->productRepository->method('get')
            ->with($productSku)
            ->willReturn($this->product);

        $this->product->expects($this->once())
            ->method('getTypeId')
            ->willReturn('bundle');

        $this->productType->expects($this->once())
            ->method('setStoreFilter')
            ->with(
                $this->storeId,
                $this->product
            );
        $this->productType->expects($this->once())
            ->method('getSelectionsCollection')
            ->with(
                $this->optionIds,
                $this->product
            )
            ->willReturn($this->selectionCollection);
        $this->productType->expects($this->once())
            ->method('getOptionsIds')
            ->with($this->product)
            ->willReturn($this->optionIds);

        $this->optionCollection->expects($this->once())
            ->method('appendSelections')
            ->with($this->selectionCollection)
            ->willReturn([$this->option]);

        $this->option->method('getSelections')
            ->willReturn([$this->product]);
        $this->product->method('getData')
            ->willReturn([]);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($this->link, $this->anything(), LinkInterface::class)
            ->willReturnSelf();
        $this->link->expects($this->once())->method('setIsDefault')->willReturnSelf();
        $this->link->expects($this->once())->method('setQty')->willReturnSelf();
        $this->link->expects($this->once())->method('setCanChangeQuantity')->willReturnSelf();
        $this->link->expects($this->once())->method('setPrice')->willReturnSelf();
        $this->link->expects($this->once())->method('setPriceType')->willReturnSelf();
        $this->link->expects($this->once())->method('setId')->willReturnSelf();
        $this->linkFactory->expects($this->once())->method('create')->willReturn($this->link);

        $this->assertEquals([$this->link], $this->model->getChildren($productSku));
    }

    public function testGetChildrenWithOptionId()
    {
        $productSku = 'productSku';

        $this->getOptions();

        $this->productRepository->method('get')
            ->with($productSku)
            ->willReturn($this->product);

        $this->product->expects($this->once())
            ->method('getTypeId')
            ->willReturn('bundle');

        $this->productType->expects($this->once())
            ->method('setStoreFilter')
            ->with(
                $this->storeId,
                $this->product
            );
        $this->productType->expects($this->once())
            ->method('getSelectionsCollection')
            ->with(
                $this->optionIds,
                $this->product
            )
            ->willReturn($this->selectionCollection);
        $this->productType->expects($this->once())
            ->method('getOptionsIds')
            ->with($this->product)
            ->willReturn($this->optionIds);

        $this->optionCollection->expects($this->once())
            ->method('appendSelections')
            ->with($this->selectionCollection)
            ->willReturn([$this->option]);

        $this->option->method('getOptionId')
            ->willReturn(10);
        $this->option->expects($this->once())
            ->method('getSelections')
            ->willReturn([1, 2]);

        $this->dataObjectHelperMock->expects($this->never())->method('populateWithArray');

        $this->assertEquals([], $this->model->getChildren($productSku, 1));
    }

    public function testGetChildrenException()
    {
        $this->expectException(InputException::class);

        $productSku = 'productSku';

        $this->productRepository->expects($this->once())
            ->method('get')
            ->with($productSku)
            ->willReturn($this->product);

        $this->product->expects($this->once())
            ->method('getTypeId')
            ->willReturn('simple');

        $this->assertEquals([$this->link], $this->model->getChildren($productSku));
    }

    public function testAddChildToNotBundleProduct()
    {
        $this->expectException(InputException::class);

        $productLink = $this->getMockForAbstractClass(LinkInterface::class);
        $productLink->method('getOptionId')
            ->willReturn(1);

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_SIMPLE);
        $this->model->addChild($productMock, 1, $productLink);
    }

    public function testAddChildNonExistingOption()
    {
        $this->expectException(InputException::class);

        $productLink = $this->getMockForAbstractClass(LinkInterface::class);
        $productLink->method('getOptionId')->willReturn(1);

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())->method('getTypeId')->willReturn(Type::TYPE_BUNDLE);

        $store = $this->createMock(Store::class);
        $this->storeManagerMock->method('getStore')->willReturn($store);
        $store->method('getId')->willReturn(0);

        $emptyOption = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();
        $emptyOption->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $optionsCollectionMock = $this->createMock(OptionCollection::class);
        $optionsCollectionMock->expects($this->once())
            ->method('setIdFilter')
            ->with(1)
            ->willReturnSelf();
        $optionsCollectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($emptyOption);

        $this->optionCollectionFactoryMock
            ->method('create')
            ->willReturn($optionsCollectionMock);
        $this->model->addChild($productMock, 1, $productLink);
    }

    public function testAddChildLinkedProductIsComposite()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('The bundle product can\'t contain another composite product.');

        $productLink = $this->getMockForAbstractClass(LinkInterface::class);
        $productLink->method('getSku')->willReturn('linked_product_sku');
        $productLink->method('getOptionId')->willReturn(1);

        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn($this->linkField);
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_BUNDLE);
        $productMock->method('getData')
            ->with($this->linkField)
            ->willReturn($this->linkField);

        $linkedProductMock = $this->createMock(Product::class);
        $linkedProductMock->method('getId')->willReturn(13);
        $linkedProductMock->expects($this->once())
            ->method('isComposite')
            ->willReturn(true);
        $this->productRepository->expects($this->once())
            ->method('get')
            ->with('linked_product_sku')
            ->willReturn($linkedProductMock);

        $store = $this->createMock(Store::class);
        $this->storeManagerMock
            ->method('getStore')
            ->willReturn($store);
        $store->method('getId')
            ->willReturn(0);

        $option = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getId')->willReturn(1);

        $optionsCollectionMock = $this->createMock(OptionCollection::class);
        $optionsCollectionMock->expects($this->once())
            ->method('setIdFilter')
            ->with('1')
            ->willReturnSelf();
        $optionsCollectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($option);
        $this->optionCollectionFactoryMock->method('create')
            ->willReturn($optionsCollectionMock);

        $bundle = $this->createMock(Bundle::class);
        $bundle->expects($this->once())->method('getSelectionsData')->with($this->linkField)->willReturn([]);
        $this->bundleFactoryMock->expects($this->once())->method('create')->willReturn($bundle);
        $this->model->addChild($productMock, 1, $productLink);
    }

    public function testAddChildProductAlreadyExistsInOption()
    {
        $this->expectException(CouldNotSaveException::class);

        $productLink = $this->getMockBuilder(LinkInterface::class)
            ->setMethods(['getSku', 'getOptionId', 'getSelectionId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productLink->method('getSku')->willReturn('linked_product_sku');
        $productLink->method('getOptionId')->willReturn(1);
        $productLink->method('getSelectionId')->willReturn(1);

        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn($this->linkField);
        $productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['getCopyFromView'])
            ->onlyMethods(['getTypeId', 'getData', 'getTypeInstance', 'getSku'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())->method('getTypeId')->willReturn(
            Type::TYPE_BUNDLE
        );

        $productMock->method('getData')
            ->with($this->linkField)
            ->willReturn($this->linkField);
        $productMock->method('getCopyFromView')
            ->willReturn(false);

        $linkedProductMock = $this->createMock(Product::class);
        $linkedProductMock->method('getEntityId')
            ->willReturn(13);
        $linkedProductMock->expects($this->once())
            ->method('isComposite')
            ->willReturn(false);
        $this->productRepository->expects($this->once())
            ->method('get')
            ->with('linked_product_sku')
            ->willReturn($linkedProductMock);

        $store = $this->createMock(Store::class);
        $this->storeManagerMock->method('getStore')->willReturn($store);
        $store->method('getId')->willReturn(0);

        $option = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $optionsCollectionMock = $this->createMock(OptionCollection::class);
        $optionsCollectionMock->expects($this->once())
            ->method('setIdFilter')
            ->with(1)
            ->willReturnSelf();
        $optionsCollectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($option);
        $this->optionCollectionFactoryMock->method('create')
            ->willReturn($optionsCollectionMock);

        $selections = [
            ['option_id' => 1, 'product_id' => 12, 'parent_product_id' => 'product_id'],
            ['option_id' => 1, 'product_id' => 13, 'parent_product_id' => 'product_id'],
        ];
        $bundle = $this->createMock(Bundle::class);
        $bundle->expects($this->once())
            ->method('getSelectionsData')
            ->with($this->linkField)
            ->willReturn($selections);
        $this->bundleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($bundle);
        $this->model->addChild($productMock, 1, $productLink);
    }

    public function testAddChildCouldNotSave()
    {
        $this->expectException(CouldNotSaveException::class);

        $productLink = $this->getMockBuilder(LinkInterface::class)
            ->setMethods(['getSku', 'getOptionId', 'getSelectionId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productLink->method('getSku')->willReturn('linked_product_sku');
        $productLink->method('getOptionId')->willReturn(1);
        $productLink->method('getSelectionId')->willReturn(1);

        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn($this->linkField);
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_BUNDLE);
        $productMock
            ->method('getData')
            ->with($this->linkField)
            ->willReturn($this->linkField);

        $linkedProductMock = $this->createMock(Product::class);
        $linkedProductMock->method('getId')->willReturn(13);
        $linkedProductMock->expects($this->once())
            ->method('isComposite')
            ->willReturn(false);
        $this->productRepository
            ->expects($this->once())
            ->method('get')
            ->with('linked_product_sku')
            ->willReturn($linkedProductMock);

        $store = $this->createMock(Store::class);
        $this->storeManagerMock->method('getStore')->willReturn($store);
        $store->method('getId')->willReturn(0);

        $option = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getId')->willReturn(1);

        $optionsCollectionMock = $this->createMock(OptionCollection::class);
        $optionsCollectionMock->expects($this->once())
            ->method('setIdFilter')
            ->with(1)
            ->willReturnSelf();
        $optionsCollectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($option);
        $this->optionCollectionFactoryMock->method('create')
            ->willReturn($optionsCollectionMock);

        $selections = [
            ['option_id' => 1, 'product_id' => 11],
            ['option_id' => 1, 'product_id' => 12],
        ];
        $bundle = $this->createMock(Bundle::class);
        $bundle->expects($this->once())->method('getSelectionsData')
            ->with($this->linkField)
            ->willReturn($selections);
        $this->bundleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($bundle);

        $selection = $this->createPartialMock(Selection::class, ['save']);
        $selection->expects($this->once())->method('save')
            ->willReturnCallback(
                static function () {
                    throw new Exception('message');
                }
            );
        $this->bundleSelectionMock->expects($this->once())
            ->method('create')
            ->willReturn($selection);
        $this->model->addChild($productMock, 1, $productLink);
    }

    public function testAddChild()
    {
        $productLink = $this->getMockBuilder(LinkInterface::class)
            ->setMethods(['getSku', 'getOptionId', 'getSelectionId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productLink->method('getSku')->willReturn('linked_product_sku');
        $productLink->method('getOptionId')->willReturn(1);
        $productLink->method('getSelectionId')->willReturn(1);

        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn($this->linkField);
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())->method('getTypeId')->willReturn(Type::TYPE_BUNDLE);
        $productMock
            ->method('getData')
            ->with($this->linkField)
            ->willReturn($this->linkField);

        $linkedProductMock = $this->createMock(Product::class);
        $linkedProductMock->method('getId')->willReturn(13);
        $linkedProductMock->expects($this->once())->method('isComposite')->willReturn(false);
        $this->productRepository
            ->expects($this->once())
            ->method('get')
            ->with('linked_product_sku')
            ->willReturn($linkedProductMock);

        $store = $this->createMock(Store::class);
        $this->storeManagerMock->method('getStore')->willReturn($store);
        $store->method('getId')->willReturn(0);

        $option = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getId')->willReturn(1);

        $optionsCollectionMock = $this->createMock(OptionCollection::class);
        $optionsCollectionMock->expects($this->once())
            ->method('setIdFilter')
            ->with(1)
            ->willReturnSelf();
        $optionsCollectionMock->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($option);
        $this->optionCollectionFactoryMock->method('create')
            ->willReturn($optionsCollectionMock);

        $selections = [
            ['option_id' => 1, 'product_id' => 11],
            ['option_id' => 1, 'product_id' => 12],
        ];
        $bundle = $this->createMock(Bundle::class);
        $bundle->expects($this->once())->method('getSelectionsData')
            ->with($this->linkField)
            ->willReturn($selections);
        $this->bundleFactoryMock->expects($this->once())->method('create')->willReturn($bundle);

        $selection = $this->createPartialMock(Selection::class, ['save', 'getId']);
        $selection->expects($this->once())->method('save');
        $selection->expects($this->once())->method('getId')->willReturn(42);
        $this->bundleSelectionMock->expects($this->once())->method('create')->willReturn($selection);
        $result = $this->model->addChild($productMock, 1, $productLink);
        $this->assertEquals(42, $result);
    }

    public function testSaveChild()
    {
        $id = 12;
        $optionId = 1;
        $position = 3;
        $qty = 2;
        $priceType = 1;
        $price = 10.5;
        $canChangeQuantity = true;
        $isDefault = true;
        $linkProductId = 45;
        $parentProductId = 32;
        $bundleProductSku = 'bundleProductSku';

        $productLink = $this->getMockBuilder(LinkInterface::class)
            ->setMethods(['getSku', 'getOptionId', 'getSelectionId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productLink->method('getSku')->willReturn('linked_product_sku');
        $productLink->method('getId')->willReturn($id);
        $productLink->method('getOptionId')->willReturn($optionId);
        $productLink->method('getPosition')->willReturn($position);
        $productLink->method('getQty')->willReturn($qty);
        $productLink->method('getPriceType')->willReturn($priceType);
        $productLink->method('getPrice')->willReturn($price);
        $productLink->method('getCanChangeQuantity')
            ->willReturn($canChangeQuantity);
        $productLink->method('getIsDefault')->willReturn($isDefault);
        $productLink->method('getSelectionId')->willReturn($optionId);

        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn($this->linkField);
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())->method('getTypeId')->willReturn(Type::TYPE_BUNDLE);
        $productMock
            ->method('getData')
            ->with($this->linkField)
            ->willReturn($parentProductId);

        $linkedProductMock = $this->createMock(Product::class);
        $linkedProductMock->method('getId')->willReturn($linkProductId);
        $linkedProductMock->expects($this->once())->method('isComposite')->willReturn(false);
        $this->productRepository
            ->expects($this->at(0))
            ->method('get')
            ->with($bundleProductSku)
            ->willReturn($productMock);
        $this->productRepository
            ->expects($this->at(1))
            ->method('get')
            ->with('linked_product_sku')
            ->willReturn($linkedProductMock);

        $store = $this->createMock(Store::class);
        $this->storeManagerMock->method('getStore')->willReturn($store);
        $store->method('getId')->willReturn(0);

        $selection = $this->getMockBuilder(Selection::class)
            ->addMethods(
                [
                    'setProductId',
                    'setParentProductId',
                    'setOptionId',
                    'setPosition',
                    'setSelectionQty',
                    'setSelectionPriceType',
                    'setSelectionPriceValue',
                    'setSelectionCanChangeQty',
                    'setIsDefault'
                ]
            )
            ->onlyMethods(['save', 'getId', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
        $selection->expects($this->once())->method('save');
        $selection->expects($this->once())->method('load')->with($id)->willReturnSelf();
        $selection->method('getId')->willReturn($id);
        $selection->expects($this->once())->method('setProductId')->with($linkProductId);
        $selection->expects($this->once())->method('setParentProductId')->with($parentProductId);
        $selection->expects($this->once())->method('setOptionId')->with($optionId);
        $selection->expects($this->once())->method('setPosition')->with($position);
        $selection->expects($this->once())->method('setSelectionQty')->with($qty);
        $selection->expects($this->once())->method('setSelectionPriceType')->with($priceType);
        $selection->expects($this->once())->method('setSelectionPriceValue')->with($price);
        $selection->expects($this->once())->method('setSelectionCanChangeQty')->with($canChangeQuantity);
        $selection->expects($this->once())->method('setIsDefault')->with($isDefault);

        $this->bundleSelectionMock->expects($this->once())->method('create')->willReturn($selection);
        $this->assertTrue($this->model->saveChild($bundleProductSku, $productLink));
    }

    public function testSaveChildFailedToSave()
    {
        $this->expectException(CouldNotSaveException::class);

        $id = 12;
        $linkProductId = 45;
        $parentProductId = 32;

        $productLink = $this->getMockBuilder(LinkInterface::class)
            ->setMethods(['getSku', 'getOptionId', 'getSelectionId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productLink->method('getSku')->willReturn('linked_product_sku');
        $productLink->method('getId')->willReturn($id);
        $productLink->method('getSelectionId')->willReturn(1);
        $bundleProductSku = 'bundleProductSku';

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_BUNDLE);
        $productMock->method('getId')
            ->willReturn($parentProductId);

        $linkedProductMock = $this->createMock(Product::class);
        $linkedProductMock->method('getId')->willReturn($linkProductId);
        $linkedProductMock->expects($this->once())
            ->method('isComposite')
            ->willReturn(false);
        $this->productRepository->expects($this->at(0))
            ->method('get')
            ->with($bundleProductSku)
            ->willReturn($productMock);
        $this->productRepository->expects($this->at(1))
            ->method('get')
            ->with('linked_product_sku')
            ->willReturn($linkedProductMock);

        $store = $this->createMock(Store::class);
        $this->storeManagerMock->method('getStore')
            ->willReturn($store);
        $store->method('getId')
            ->willReturn(0);

        $selection = $this->getMockBuilder(Selection::class)
            ->addMethods(
                [
                    'setProductId',
                    'setParentProductId',
                    'setSelectionId',
                    'setOptionId',
                    'setPosition',
                    'setSelectionQty',
                    'setSelectionPriceType',
                    'setSelectionPriceValue',
                    'setSelectionCanChangeQty',
                    'setIsDefault'
                ]
            )
            ->onlyMethods(['save', 'getId', 'load'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockException = $this->createMock(Exception::class);
        $selection->expects($this->once())
            ->method('save')
            ->willThrowException($mockException);
        $selection->expects($this->once())
            ->method('load')
            ->with($id)
            ->willReturnSelf();
        $selection->method('getId')
            ->willReturn($id);
        $selection->expects($this->once())
            ->method('setProductId')
            ->with($linkProductId);

        $this->bundleSelectionMock->expects($this->once())
            ->method('create')
            ->willReturn($selection);
        $this->model->saveChild($bundleProductSku, $productLink);
    }

    public function testSaveChildWithoutId()
    {
        $this->expectException(InputException::class);

        $bundleProductSku = 'bundleSku';
        $linkedProductSku = 'simple';
        $productLink = $this->getMockForAbstractClass(LinkInterface::class);
        $productLink->method('getId')->willReturn(null);
        $productLink->method('getSku')->willReturn($linkedProductSku);

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_BUNDLE);

        $linkedProductMock = $this->createMock(Product::class);
        $linkedProductMock->expects($this->once())
            ->method('isComposite')
            ->willReturn(false);
        $this->productRepository->expects($this->at(0))
            ->method('get')
            ->with($bundleProductSku)
            ->willReturn($productMock);
        $this->productRepository->expects($this->at(1))
            ->method('get')
            ->with($linkedProductSku)
            ->willReturn($linkedProductMock);

        $this->model->saveChild($bundleProductSku, $productLink);
    }

    public function testSaveChildWithInvalidId()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage(
            'The product link with the "12345" ID field wasn\'t found. Verify the ID and try again.'
        );

        $id = 12345;
        $linkedProductSku = 'simple';
        $bundleProductSku = 'bundleProductSku';
        $productLink = $this->getMockForAbstractClass(LinkInterface::class);
        $productLink->method('getId')->willReturn($id);
        $productLink->method('getSku')->willReturn($linkedProductSku);

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_BUNDLE);

        $linkedProductMock = $this->createMock(Product::class);
        $linkedProductMock->expects($this->once())
            ->method('isComposite')
            ->willReturn(false);
        $this->productRepository->expects($this->at(0))
            ->method('get')
            ->with($bundleProductSku)
            ->willReturn($productMock);
        $this->productRepository->expects($this->at(1))
            ->method('get')
            ->with($linkedProductSku)
            ->willReturn($linkedProductMock);

        $selection = $this->createPartialMock(
            Selection::class,
            [
                'getId',
                'load',
            ]
        );
        $selection->expects($this->once())
            ->method('load')
            ->with($id)
            ->willReturnSelf();
        $selection->method('getId')->willReturn(null);

        $this->bundleSelectionMock->expects($this->once())
            ->method('create')
            ->willReturn($selection);

        $this->model->saveChild($bundleProductSku, $productLink);
    }

    public function testSaveChildWithCompositeProductLink()
    {
        $this->expectException(InputException::class);

        $bundleProductSku = 'bundleProductSku';
        $id = 12;
        $linkedProductSku = 'simple';
        $productLink = $this->getMockForAbstractClass(LinkInterface::class);
        $productLink->method('getId')->willReturn($id);
        $productLink->method('getSku')->willReturn($linkedProductSku);

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())->method('getTypeId')->willReturn(Type::TYPE_BUNDLE);

        $linkedProductMock = $this->createMock(Product::class);
        $linkedProductMock->expects($this->once())->method('isComposite')->willReturn(true);
        $this->productRepository->expects($this->at(0))
            ->method('get')
            ->with($bundleProductSku)
            ->willReturn($productMock);
        $this->productRepository->expects($this->at(1))
            ->method('get')
            ->with($linkedProductSku)
            ->willReturn($linkedProductMock);

        $this->model->saveChild($bundleProductSku, $productLink);
    }

    public function testSaveChildWithSimpleProduct()
    {
        $this->expectException(InputException::class);

        $id = 12;
        $linkedProductSku = 'simple';
        $bundleProductSku = 'bundleProductSku';

        $productLink = $this->getMockForAbstractClass(LinkInterface::class);
        $productLink->method('getId')->willReturn($id);
        $productLink->method('getSku')->willReturn($linkedProductSku);

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())->method('getTypeId')->willReturn(Type::TYPE_SIMPLE);

        $this->productRepository->expects($this->once())
            ->method('get')
            ->with($bundleProductSku)
            ->willReturn($productMock);

        $this->model->saveChild($bundleProductSku, $productLink);
    }

    public function testRemoveChild()
    {
        $this->productRepository->method('get')->willReturn($this->product);
        $bundle = $this->createMock(Bundle::class);
        $this->bundleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($bundle);
        $productSku = 'productSku';
        $optionId = 1;
        $productId = 1;
        $childSku = 'childSku';

        $this->product->method('getTypeId')
            ->willReturn(Type::TYPE_BUNDLE);

        $this->getRemoveOptions();

        $selection = $this->getMockBuilder(Selection::class)
            ->setMethods(['getSku', 'getOptionId', 'getSelectionId', 'getProductId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selection->method('getSku')->willReturn($childSku);
        $selection->method('getOptionId')->willReturn($optionId);
        $selection->method('getSelectionId')->willReturn(55);
        $selection->method('getProductId')->willReturn($productId);

        $this->option->method('getSelections')->willReturn([$selection]);
        $this->metadataMock->method('getLinkField')->willReturn($this->linkField);
        $this->product->method('getData')
            ->with($this->linkField)
            ->willReturn(3);

        $bundle->expects($this->once())->method('dropAllUnneededSelections')->with(3, []);
        $bundle->expects($this->once())->method('removeProductRelations')->with(3, [$productId]);
        //Params come in lowercase to method
        $this->assertTrue($this->model->removeChild($productSku, $optionId, $childSku));
    }

    public function testRemoveChildForbidden()
    {
        $this->expectException(InputException::class);

        $this->productRepository->method('get')->willReturn($this->product);
        $productSku = 'productSku';
        $optionId = 1;
        $childSku = 'childSku';
        $this->product->method('getTypeId')
            ->willReturn(Type::TYPE_SIMPLE);
        $this->model->removeChild($productSku, $optionId, $childSku);
    }

    public function testRemoveChildInvalidOptionId()
    {
        $this->expectException(NoSuchEntityException::class);

        $this->productRepository->method('get')->willReturn($this->product);
        $productSku = 'productSku';
        $optionId = 1;
        $childSku = 'childSku';

        $this->product->method('getTypeId')
            ->willReturn(Type::TYPE_BUNDLE);

        $this->getRemoveOptions();

        $selection = $this->getMockBuilder(Selection::class)
            ->setMethods(['getSku', 'getOptionId', 'getSelectionId', 'getProductId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selection->method('getSku')->willReturn($childSku);
        $selection->method('getOptionId')->willReturn($optionId + 1);
        $selection->method('getSelectionId')->willReturn(55);
        $selection->method('getProductId')->willReturn(1);

        $this->option->method('getSelections')->willReturn([$selection]);
        $this->model->removeChild($productSku, $optionId, $childSku);
    }

    public function testRemoveChildInvalidChildSku()
    {
        $this->expectException(NoSuchEntityException::class);

        $this->productRepository->method('get')->willReturn($this->product);
        $productSku = 'productSku';
        $optionId = 1;
        $childSku = 'childSku';

        $this->product->method('getTypeId')
            ->willReturn(Type::TYPE_BUNDLE);

        $this->getRemoveOptions();

        $selection = $this->getMockBuilder(Selection::class)
            ->setMethods(['getSku', 'getOptionId', 'getSelectionId', 'getProductId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selection->method('getSku')->willReturn($childSku . '_invalid');
        $selection->method('getOptionId')->willReturn($optionId);
        $selection->method('getSelectionId')->willReturn(55);
        $selection->method('getProductId')->willReturn(1);

        $this->option->method('getSelections')
            ->willReturn([$selection]);
        $this->model->removeChild($productSku, $optionId, $childSku);
    }

    private function getOptions()
    {
        $this->product->method('getTypeInstance')
            ->willReturn($this->productType);
        $this->product->expects($this->once())
            ->method('getStoreId')
            ->willReturn($this->storeId);
        $this->productType->expects($this->once())
            ->method('setStoreFilter')
            ->with($this->storeId, $this->product);

        $this->productType->expects($this->once())
            ->method('getOptionsCollection')
            ->with($this->product)
            ->willReturn($this->optionCollection);
    }

    public function getRemoveOptions()
    {
        $this->product->method('getTypeInstance')
            ->willReturn($this->productType);
        $this->product->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);

        $this->productType->expects($this->once())->method('setStoreFilter');
        $this->productType->expects($this->once())->method('getOptionsCollection')
            ->with($this->product)
            ->willReturn($this->optionCollection);

        $this->productType->expects($this->once())
            ->method('getOptionsIds')
            ->with($this->product)
            ->willReturn([1, 2, 3]);

        $this->productType->expects($this->once())
            ->method('getSelectionsCollection')
            ->willReturn([]);

        $this->optionCollection->method('appendSelections')
            ->with([], true)
            ->willReturn([$this->option]);
    }
}
