<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Bundle\Test\Unit\Model;

use Magento\Bundle\Model\LinkManagement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class LinkManagementTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LinkManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Model\LinkManagement
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\ProductRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type\Interceptor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productType;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Option\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionCollection;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Selection\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectionCollection;

    /**
     * @var \Magento\Bundle\Model\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $option;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Bundle\Model\SelectionFactory
     */
    protected $bundleSelectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Bundle\Model\ResourceModel\BundleFactory
     */
    protected $bundleFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Bundle\Model\ResourceModel\Option\CollectionFactory
     */
    protected $optionCollectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $link;

    /**
     * @var int
     */
    protected $storeId = 2;

    /**
     * @var array
     */
    protected $optionIds = [1, 2, 3];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataMock;

    /**
     * @var string
     */
    protected $linkField = 'product_id';

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Model\ProductRepository::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productType = $this->getMockBuilder(\Magento\Bundle\Model\Product\Type\Interceptor::class)
            ->setMethods(['getOptionsCollection', 'setStoreFilter', 'getSelectionsCollection', 'getOptionsIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->option = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)
            ->setMethods(['getSelections', 'getOptionId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->setMethods(['appendSelections'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectionCollection = $this->getMockBuilder(
            \Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getTypeInstance', 'getStoreId', 'getTypeId', '__wakeup', 'getId', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->link = $this->getMockBuilder(\Magento\Bundle\Api\Data\LinkInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->linkFactory = $this->getMockBuilder(\Magento\Bundle\Api\Data\LinkInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->bundleSelectionMock = $this->getMock(
            \Magento\Bundle\Model\SelectionFactory::class, ['create'], [], '', false
        );
        $this->bundleFactoryMock = $this->getMock(
            \Magento\Bundle\Model\ResourceModel\BundleFactory::class, ['create'], [], '', false
        );
        $this->optionCollectionFactoryMock = $this->getMock(
            \Magento\Bundle\Model\ResourceModel\Option\CollectionFactory::class, ['create'], [], '', false
        );
        $this->storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class, [], [], '', false);
        $this->metadataPoolMock = $this->getMockBuilder(\Magento\Framework\EntityManager\MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataMock = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock->expects($this->any())->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($this->metadataMock);

        $this->dataObjectHelperMock = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
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
            ]
        );
        $refClass = new \ReflectionClass(LinkManagement::class);
        $refProperty = $refClass->getProperty('metadataPool');
        $refProperty->setAccessible(true);
        $refProperty->setValue($this->model, $this->metadataPoolMock);
    }

    public function testGetChildren()
    {
        $productSku = 'productSku';

        $this->getOptions();

        $this->productRepository->expects($this->any())->method('get')->with($this->equalTo($productSku))
            ->will($this->returnValue($this->product));

        $this->product->expects($this->once())->method('getTypeId')->will($this->returnValue('bundle'));

        $this->productType->expects($this->once())->method('setStoreFilter')->with(
            $this->equalTo($this->storeId),
            $this->product
        );
        $this->productType->expects($this->once())->method('getSelectionsCollection')
            ->with($this->equalTo($this->optionIds), $this->equalTo($this->product))
            ->will($this->returnValue($this->selectionCollection));
        $this->productType->expects($this->once())->method('getOptionsIds')->with($this->equalTo($this->product))
            ->will($this->returnValue($this->optionIds));

        $this->optionCollection->expects($this->once())->method('appendSelections')
            ->with($this->equalTo($this->selectionCollection))
            ->will($this->returnValue([$this->option]));

        $this->option->expects($this->any())->method('getSelections')->willReturn([$this->product]);
        $this->product->expects($this->any())->method('getData')->willReturn([]);

        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($this->link, $this->anything(), \Magento\Bundle\Api\Data\LinkInterface::class)
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

        $this->productRepository->expects($this->any())->method('get')->with($this->equalTo($productSku))
            ->will($this->returnValue($this->product));

        $this->product->expects($this->once())->method('getTypeId')->will($this->returnValue('bundle'));

        $this->productType->expects($this->once())->method('setStoreFilter')->with(
            $this->equalTo($this->storeId),
            $this->product
        );
        $this->productType->expects($this->once())->method('getSelectionsCollection')
            ->with($this->equalTo($this->optionIds), $this->equalTo($this->product))
            ->will($this->returnValue($this->selectionCollection));
        $this->productType->expects($this->once())->method('getOptionsIds')->with($this->equalTo($this->product))
            ->will($this->returnValue($this->optionIds));

        $this->optionCollection->expects($this->once())->method('appendSelections')
            ->with($this->equalTo($this->selectionCollection))
            ->will($this->returnValue([$this->option]));

        $this->option->expects($this->any())->method('getOptionId')->will($this->returnValue(10));
        $this->option->expects($this->once())->method('getSelections')->willReturn([1, 2]);

        $this->dataObjectHelperMock->expects($this->never())->method('populateWithArray');

        $this->assertEquals([], $this->model->getChildren($productSku, 1));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testGetChildrenException()
    {
        $productSku = 'productSku';

        $this->productRepository->expects($this->once())->method('get')->with($this->equalTo($productSku))
            ->will($this->returnValue($this->product));

        $this->product->expects($this->once())->method('getTypeId')->will($this->returnValue('simple'));

        $this->assertEquals([$this->link], $this->model->getChildren($productSku));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testAddChildToNotBundleProduct()
    {
        $productLink = $this->getMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
        ));
        $this->model->addChild($productMock, 1, $productLink);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testAddChildNonExistingOption()
    {
        $productLink = $this->getMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));

        $store = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue(0));

        $emptyOption = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();
        $emptyOption->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(null));

        $optionsCollectionMock = $this->getMock(
            \Magento\Bundle\Model\ResourceModel\Option\Collection::class, [], [], '', false
        );
        $optionsCollectionMock->expects($this->once())
            ->method('setIdFilter')
            ->with($this->equalTo(1))
            ->will($this->returnSelf());
        $optionsCollectionMock->expects($this->once())
            ->method('getFirstItem')
           ->will($this->returnValue($emptyOption));

        $this->optionCollectionFactoryMock->expects($this->any())->method('create')->will(
            $this->returnValue($optionsCollectionMock)
        );
        $this->model->addChild($productMock, 1, $productLink);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Bundle product could not contain another composite product
     */
    public function testAddChildLinkedProductIsComposite()
    {
        $productLink = $this->getMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue('linked_product_sku'));
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn($this->linkField);
        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));
        $productMock->expects($this->any())
            ->method('getData')
            ->with($this->linkField)
            ->willReturn($this->linkField);

        $linkedProductMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $linkedProductMock->expects($this->any())->method('getId')->will($this->returnValue(13));
        $linkedProductMock->expects($this->once())->method('isComposite')->will($this->returnValue(true));
        $this->productRepository
            ->expects($this->once())
            ->method('get')
            ->with('linked_product_sku')
            ->will($this->returnValue($linkedProductMock));

        $store = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue(0));

        $option = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getId')->will($this->returnValue(1));

        $optionsCollectionMock = $this->getMock(
            \Magento\Bundle\Model\ResourceModel\Option\Collection::class,
            [],
            [],
            '',
            false
        );
        $optionsCollectionMock->expects($this->once())
            ->method('setIdFilter')
            ->with($this->equalTo('1'))
            ->will($this->returnSelf());
        $optionsCollectionMock->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($option));
        $this->optionCollectionFactoryMock->expects($this->any())->method('create')->will(
            $this->returnValue($optionsCollectionMock)
        );

        $bundle = $this->getMock(\Magento\Bundle\Model\ResourceModel\Bundle::class, [], [], '', false);
        $bundle->expects($this->once())->method('getSelectionsData')->with($this->linkField)->willReturn([]);
        $this->bundleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($bundle));
        $this->model->addChild($productMock, 1, $productLink);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testAddChildProductAlreadyExistsInOption()
    {
        $productLink = $this->getMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue('linked_product_sku'));
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn($this->linkField);
        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));
        $productMock->expects($this->any())
            ->method('getData')
            ->with($this->linkField)
            ->willReturn($this->linkField);
        $productMock->expects($this->any())->method('getCopyFromView')->will($this->returnValue(false));

        $linkedProductMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $linkedProductMock->expects($this->any())->method('getEntityId')->will($this->returnValue(13));
        $linkedProductMock->expects($this->once())->method('isComposite')->will($this->returnValue(false));
        $this->productRepository
            ->expects($this->once())
            ->method('get')
            ->with('linked_product_sku')
            ->will($this->returnValue($linkedProductMock));

        $store = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue(0));

        $option = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getId')->will($this->returnValue(1));

        $optionsCollectionMock = $this->getMock(
            \Magento\Bundle\Model\ResourceModel\Option\Collection::class,
            [],
            [],
            '',
            false
        );
        $optionsCollectionMock->expects($this->once())
            ->method('setIdFilter')
            ->with($this->equalTo(1))
            ->will($this->returnSelf());
        $optionsCollectionMock->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($option));
        $this->optionCollectionFactoryMock->expects($this->any())->method('create')->will(
            $this->returnValue($optionsCollectionMock)
        );

        $selections = [
            ['option_id' => 1, 'product_id' => 12],
            ['option_id' => 1, 'product_id' => 13],
        ];
        $bundle = $this->getMock(\Magento\Bundle\Model\ResourceModel\Bundle::class, [], [], '', false);
        $bundle->expects($this->once())->method('getSelectionsData')
            ->with($this->linkField)
            ->will($this->returnValue($selections));
        $this->bundleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($bundle));
        $this->model->addChild($productMock, 1, $productLink);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testAddChildCouldNotSave()
    {
        $productLink = $this->getMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue('linked_product_sku'));
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn($this->linkField);
        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));
        $productMock->expects($this->any())
            ->method('getData')
            ->with($this->linkField)
            ->willReturn($this->linkField);

        $linkedProductMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $linkedProductMock->expects($this->any())->method('getId')->will($this->returnValue(13));
        $linkedProductMock->expects($this->once())->method('isComposite')->will($this->returnValue(false));
        $this->productRepository
            ->expects($this->once())
            ->method('get')
            ->with('linked_product_sku')
            ->will($this->returnValue($linkedProductMock));

        $store = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue(0));

        $option = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getId')->will($this->returnValue(1));

        $optionsCollectionMock = $this->getMock(
            \Magento\Bundle\Model\ResourceModel\Option\Collection::class, [], [], '', false
        );
        $optionsCollectionMock->expects($this->once())
            ->method('setIdFilter')
            ->with($this->equalTo(1))
            ->will($this->returnSelf());
        $optionsCollectionMock->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($option));
        $this->optionCollectionFactoryMock->expects($this->any())->method('create')->will(
            $this->returnValue($optionsCollectionMock)
        );

        $selections = [
            ['option_id' => 1, 'product_id' => 11],
            ['option_id' => 1, 'product_id' => 12],
        ];
        $bundle = $this->getMock(\Magento\Bundle\Model\ResourceModel\Bundle::class, [], [], '', false);
        $bundle->expects($this->once())->method('getSelectionsData')
            ->with($this->linkField)
            ->will($this->returnValue($selections));
        $this->bundleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($bundle));

        $selection = $this->getMock(\Magento\Bundle\Model\Selection::class, ['save'], [], '', false);
        $selection->expects($this->once())->method('save')
            ->will(
                $this->returnCallback(
                    function () {
                        throw new \Exception('message');
                    }
                )
            );
        $this->bundleSelectionMock->expects($this->once())->method('create')->will($this->returnValue($selection));
        $this->model->addChild($productMock, 1, $productLink);
    }

    public function testAddChild()
    {
        $productLink = $this->getMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue('linked_product_sku'));
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn($this->linkField);
        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));
        $productMock->expects($this->any())
            ->method('getData')
            ->with($this->linkField)
            ->willReturn($this->linkField);

        $linkedProductMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $linkedProductMock->expects($this->any())->method('getId')->will($this->returnValue(13));
        $linkedProductMock->expects($this->once())->method('isComposite')->will($this->returnValue(false));
        $this->productRepository
            ->expects($this->once())
            ->method('get')
            ->with('linked_product_sku')
            ->will($this->returnValue($linkedProductMock));

        $store = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue(0));

        $option = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getId')->will($this->returnValue(1));

        $optionsCollectionMock = $this->getMock(
            \Magento\Bundle\Model\ResourceModel\Option\Collection::class, [], [], '', false
        );
        $optionsCollectionMock->expects($this->once())
            ->method('setIdFilter')
            ->with($this->equalTo(1))
            ->will($this->returnSelf());
        $optionsCollectionMock->expects($this->once())
            ->method('getFirstItem')
            ->will($this->returnValue($option));
        $this->optionCollectionFactoryMock->expects($this->any())->method('create')->will(
            $this->returnValue($optionsCollectionMock)
        );

        $selections = [
            ['option_id' => 1, 'product_id' => 11],
            ['option_id' => 1, 'product_id' => 12],
        ];
        $bundle = $this->getMock(\Magento\Bundle\Model\ResourceModel\Bundle::class, [], [], '', false);
        $bundle->expects($this->once())->method('getSelectionsData')
            ->with($this->linkField)
            ->will($this->returnValue($selections));
        $this->bundleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($bundle));

        $selection = $this->getMock(\Magento\Bundle\Model\Selection::class, ['save', 'getId'], [], '', false);
        $selection->expects($this->once())->method('save');
        $selection->expects($this->once())->method('getId')->will($this->returnValue(42));
        $this->bundleSelectionMock->expects($this->once())->method('create')->will($this->returnValue($selection));
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

        $productLink = $this->getMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue('linked_product_sku'));
        $productLink->expects($this->any())->method('getId')->will($this->returnValue($id));
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue($optionId));
        $productLink->expects($this->any())->method('getPosition')->will($this->returnValue($position));
        $productLink->expects($this->any())->method('getQty')->will($this->returnValue($qty));
        $productLink->expects($this->any())->method('getPriceType')->will($this->returnValue($priceType));
        $productLink->expects($this->any())->method('getPrice')->will($this->returnValue($price));
        $productLink->expects($this->any())->method('getCanChangeQuantity')->will($this->returnValue($canChangeQuantity));
        $productLink->expects($this->any())->method('getIsDefault')->will($this->returnValue($isDefault));

        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn($this->linkField);
        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));
        $productMock->expects($this->any())
            ->method('getData')
            ->with($this->linkField)
            ->willReturn($parentProductId);

        $linkedProductMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $linkedProductMock->expects($this->any())->method('getId')->will($this->returnValue($linkProductId));
        $linkedProductMock->expects($this->once())->method('isComposite')->will($this->returnValue(false));
        $this->productRepository
            ->expects($this->at(0))
            ->method('get')
            ->with($bundleProductSku)
            ->will($this->returnValue($productMock));
        $this->productRepository
            ->expects($this->at(1))
            ->method('get')
            ->with('linked_product_sku')
            ->will($this->returnValue($linkedProductMock));

        $store = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue(0));

        $selection = $this->getMock(
            \Magento\Bundle\Model\Selection::class,
            [
                'save',
                'getId',
                'load',
                'setProductId',
                'setParentProductId',
                'setOptionId',
                'setPosition',
                'setSelectionQty',
                'setSelectionPriceType',
                'setSelectionPriceValue',
                'setSelectionCanChangeQty',
                'setIsDefault'
            ],
            [],
            '',
            false
        );
        $selection->expects($this->once())->method('save');
        $selection->expects($this->once())->method('load')->with($id)->will($this->returnSelf());
        $selection->expects($this->any())->method('getId')->will($this->returnValue($id));
        $selection->expects($this->once())->method('setProductId')->with($linkProductId);
        $selection->expects($this->once())->method('setParentProductId')->with($parentProductId);
        $selection->expects($this->once())->method('setOptionId')->with($optionId);
        $selection->expects($this->once())->method('setPosition')->with($position);
        $selection->expects($this->once())->method('setSelectionQty')->with($qty);
        $selection->expects($this->once())->method('setSelectionPriceType')->with($priceType);
        $selection->expects($this->once())->method('setSelectionPriceValue')->with($price);
        $selection->expects($this->once())->method('setSelectionCanChangeQty')->with($canChangeQuantity);
        $selection->expects($this->once())->method('setIsDefault')->with($isDefault);

        $this->bundleSelectionMock->expects($this->once())->method('create')->will($this->returnValue($selection));
        $this->assertTrue($this->model->saveChild($bundleProductSku, $productLink));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSaveChildFailedToSave()
    {
        $id = 12;
        $linkProductId = 45;
        $parentProductId = 32;
        $productLink = $this->getMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue('linked_product_sku'));
        $productLink->expects($this->any())->method('getId')->will($this->returnValue($id));
        $bundleProductSku = 'bundleProductSku';

        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));
        $productMock->expects($this->any())->method('getId')->will($this->returnValue($parentProductId));

        $linkedProductMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $linkedProductMock->expects($this->any())->method('getId')->will($this->returnValue($linkProductId));
        $linkedProductMock->expects($this->once())->method('isComposite')->will($this->returnValue(false));
        $this->productRepository
            ->expects($this->at(0))
            ->method('get')
            ->with($bundleProductSku)
            ->will($this->returnValue($productMock));
        $this->productRepository
            ->expects($this->at(1))
            ->method('get')
            ->with('linked_product_sku')
            ->will($this->returnValue($linkedProductMock));

        $store = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue(0));

        $selection = $this->getMock(
            \Magento\Bundle\Model\Selection::class,
            [
                'save',
                'getId',
                'load',
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
            ],
            [],
            '',
            false
        );
        $mockException = $this->getMock(\Exception::class);
        $selection->expects($this->once())->method('save')->will($this->throwException($mockException));
        $selection->expects($this->once())->method('load')->with($id)->will($this->returnSelf());
        $selection->expects($this->any())->method('getId')->will($this->returnValue($id));
        $selection->expects($this->once())->method('setProductId')->with($linkProductId);

        $this->bundleSelectionMock->expects($this->once())->method('create')->will($this->returnValue($selection));
        $this->model->saveChild($bundleProductSku, $productLink);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testSaveChildWithoutId()
    {
        $bundleProductSku = "bundleSku";
        $linkedProductSku = 'simple';
        $productLink = $this->getMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $productLink->expects($this->any())->method('getId')->will($this->returnValue(null));
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue($linkedProductSku));

        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));

        $linkedProductMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $linkedProductMock->expects($this->once())->method('isComposite')->will($this->returnValue(false));
        $this->productRepository
            ->expects($this->at(0))
            ->method('get')
            ->with($bundleProductSku)
            ->will($this->returnValue($productMock));
        $this->productRepository
            ->expects($this->at(1))
            ->method('get')
            ->with($linkedProductSku)
            ->will($this->returnValue($linkedProductMock));

        $this->model->saveChild($bundleProductSku, $productLink);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Can not find product link with id "12345"
     */
    public function testSaveChildWithInvalidId()
    {
        $id = 12345;
        $linkedProductSku = 'simple';
        $bundleProductSku = "bundleProductSku";
        $productLink = $this->getMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $productLink->expects($this->any())->method('getId')->will($this->returnValue($id));
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue($linkedProductSku));

        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));

        $linkedProductMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $linkedProductMock->expects($this->once())->method('isComposite')->will($this->returnValue(false));
        $this->productRepository
            ->expects($this->at(0))
            ->method('get')
            ->with($bundleProductSku)
            ->will($this->returnValue($productMock));
        $this->productRepository
            ->expects($this->at(1))
            ->method('get')
            ->with($linkedProductSku)
            ->will($this->returnValue($linkedProductMock));

        $selection = $this->getMock(
            \Magento\Bundle\Model\Selection::class,
            [
                'getId',
                'load',
            ],
            [],
            '',
            false
        );
        $selection->expects($this->once())->method('load')->with($id)->will($this->returnSelf());
        $selection->expects($this->any())->method('getId')->will($this->returnValue(null));

        $this->bundleSelectionMock->expects($this->once())->method('create')->will($this->returnValue($selection));

        $this->model->saveChild($bundleProductSku, $productLink);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testSaveChildWithCompositeProductLink()
    {
        $bundleProductSku = "bundleProductSku";
        $id = 12;
        $linkedProductSku = 'simple';
        $productLink = $this->getMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $productLink->expects($this->any())->method('getId')->will($this->returnValue($id));
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue($linkedProductSku));

        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));

        $linkedProductMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $linkedProductMock->expects($this->once())->method('isComposite')->will($this->returnValue(true));
        $this->productRepository
            ->expects($this->at(0))
            ->method('get')
            ->with($bundleProductSku)
            ->will($this->returnValue($productMock));
        $this->productRepository
            ->expects($this->at(1))
            ->method('get')
            ->with($linkedProductSku)
            ->will($this->returnValue($linkedProductMock));

        $this->model->saveChild($bundleProductSku, $productLink);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testSaveChildWithSimpleProduct()
    {
        $id = 12;
        $linkedProductSku = 'simple';
        $bundleProductSku = "bundleProductSku";

        $productLink = $this->getMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $productLink->expects($this->any())->method('getId')->will($this->returnValue($id));
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue($linkedProductSku));

        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
        ));

        $this->productRepository->expects($this->once())->method('get')->with($bundleProductSku)
            ->willReturn($productMock);

        $this->model->saveChild($bundleProductSku, $productLink);
    }

    public function testRemoveChild()
    {
        $this->productRepository->expects($this->any())->method('get')->will($this->returnValue($this->product));
        $bundle = $this->getMock(\Magento\Bundle\Model\ResourceModel\Bundle::class, [], [], '', false);
        $this->bundleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($bundle));
        $productSku = 'productSku';
        $optionId = 1;
        $productId = 1;
        $childSku = 'childSku';

        $this->product
            ->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE));

        $this->getRemoveOptions();

        $selection = $this->getMockBuilder(\Magento\Bundle\Model\Selection::class)
            ->setMethods(['getSku', 'getOptionId', 'getSelectionId', 'getProductId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selection->expects($this->any())->method('getSku')->will($this->returnValue($childSku));
        $selection->expects($this->any())->method('getOptionId')->will($this->returnValue($optionId));
        $selection->expects($this->any())->method('getSelectionId')->will($this->returnValue(55));
        $selection->expects($this->any())->method('getProductId')->willReturn($productId);

        $this->option->expects($this->any())->method('getSelections')->will($this->returnValue([$selection]));
        $this->metadataMock->expects($this->any())->method('getLinkField')->willReturn($this->linkField);
        $this->product->expects($this->any())
            ->method('getData')
            ->with($this->linkField)
            ->willReturn(3);

        $bundle->expects($this->once())->method('dropAllUnneededSelections')->with(3, []);
        $bundle->expects($this->once())->method('removeProductRelations')->with(3, [$productId]);
        //Params come in lowercase to method
        $this->assertTrue($this->model->removeChild($productSku, $optionId, $childSku));
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testRemoveChildForbidden()
    {
        $this->productRepository->expects($this->any())->method('get')->will($this->returnValue($this->product));
        $productSku = 'productSku';
        $optionId = 1;
        $childSku = 'childSku';
        $this->product
            ->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE));
        $this->model->removeChild($productSku, $optionId, $childSku);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRemoveChildInvalidOptionId()
    {
        $this->productRepository->expects($this->any())->method('get')->will($this->returnValue($this->product));
        $productSku = 'productSku';
        $optionId = 1;
        $childSku = 'childSku';

        $this->product
            ->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE));

        $this->getRemoveOptions();

        $selection = $this->getMockBuilder(\Magento\Bundle\Model\Selection::class)
            ->setMethods(['getSku', 'getOptionId', 'getSelectionId', 'getProductId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selection->expects($this->any())->method('getSku')->will($this->returnValue($childSku));
        $selection->expects($this->any())->method('getOptionId')->will($this->returnValue($optionId + 1));
        $selection->expects($this->any())->method('getSelectionId')->will($this->returnValue(55));
        $selection->expects($this->any())->method('getProductId')->will($this->returnValue(1));

        $this->option->expects($this->any())->method('getSelections')->will($this->returnValue([$selection]));
        $this->model->removeChild($productSku, $optionId, $childSku);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRemoveChildInvalidChildSku()
    {
        $this->productRepository->expects($this->any())->method('get')->will($this->returnValue($this->product));
        $productSku = 'productSku';
        $optionId = 1;
        $childSku = 'childSku';

        $this->product
            ->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE));

        $this->getRemoveOptions();

        $selection = $this->getMockBuilder(\Magento\Bundle\Model\Selection::class)
            ->setMethods(['getSku', 'getOptionId', 'getSelectionId', 'getProductId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selection->expects($this->any())->method('getSku')->will($this->returnValue($childSku . '_invalid'));
        $selection->expects($this->any())->method('getOptionId')->will($this->returnValue($optionId));
        $selection->expects($this->any())->method('getSelectionId')->will($this->returnValue(55));
        $selection->expects($this->any())->method('getProductId')->will($this->returnValue(1));

        $this->option->expects($this->any())->method('getSelections')->will($this->returnValue([$selection]));
        $this->model->removeChild($productSku, $optionId, $childSku);
    }

    private function getOptions()
    {
        $this->product->expects($this->any())->method('getTypeInstance')->will($this->returnValue($this->productType));
        $this->product->expects($this->once())->method('getStoreId')->will($this->returnValue($this->storeId));
        $this->productType->expects($this->once())->method('setStoreFilter')
            ->with($this->equalTo($this->storeId), $this->equalTo($this->product));

        $this->productType->expects($this->once())->method('getOptionsCollection')
            ->with($this->equalTo($this->product))
            ->will($this->returnValue($this->optionCollection));
    }

    public function getRemoveOptions()
    {
        $this->product->expects($this->any())->method('getTypeInstance')->will($this->returnValue($this->productType));
        $this->product->expects($this->once())->method('getStoreId')->will($this->returnValue(1));

        $this->productType->expects($this->once())->method('setStoreFilter');
        $this->productType->expects($this->once())->method('getOptionsCollection')
            ->with($this->equalTo($this->product))
            ->will($this->returnValue($this->optionCollection));

        $this->productType->expects($this->once())->method('getOptionsIds')->with($this->equalTo($this->product))
            ->will($this->returnValue([1, 2, 3]));

        $this->productType->expects($this->once())->method('getSelectionsCollection')
            ->will($this->returnValue([]));

        $this->optionCollection->expects($this->any())->method('appendSelections')
            ->with($this->equalTo([]), true)
            ->will($this->returnValue([$this->option]));
    }
}
