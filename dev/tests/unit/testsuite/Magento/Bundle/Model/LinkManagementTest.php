<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model;

use Magento\TestFramework\Helper\ObjectManager;

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
    protected $linkBuilder;

    /**
     * @var \Magento\Bundle\Model\Product\Type\Interceptor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productType;

    /**
     * @var \Magento\Bundle\Model\Resource\Option\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionCollection;

    /**
     * @var \Magento\Bundle\Model\Resource\Selection\Collection|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Bundle\Model\Resource\BundleFactory
     */
    protected $bundleFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Bundle\Model\Resource\Option\CollectionFactory
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

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->productRepository = $this->getMockBuilder('Magento\Catalog\Model\ProductRepository')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productType = $this->getMockBuilder('Magento\Bundle\Model\Product\Type\Interceptor')
            ->setMethods(['getOptionsCollection', 'setStoreFilter', 'getSelectionsCollection', 'getOptionsIds'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->option = $this->getMockBuilder('Magento\Bundle\Model\Option')
            ->setMethods(['getSelections', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionCollection = $this->getMockBuilder('Magento\Bundle\Model\Resource\Option\Collection')
            ->setMethods(['appendSelections'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->selectionCollection = $this->getMockBuilder('Magento\Bundle\Model\Resource\Selection\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getTypeInstance', 'getStoreId', 'getTypeId', '__wakeup', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->link = $this->getMockBuilder('\Magento\Bundle\Api\Data\LinkInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->linkBuilder = $this->getMockBuilder('\Magento\Bundle\Api\Data\LinkDataBuilder')
            ->setMethods(
                [
                    'populateWithArray',
                    'setIsDefault',
                    'setQty',
                    'setIsDefined',
                    'setPrice',
                    'setPriceType',
                    'create',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->bundleSelectionMock = $this->getMock(
            '\Magento\Bundle\Model\SelectionFactory', ['create'], [], '', false
        );
        $this->bundleFactoryMock = $this->getMock(
            '\Magento\Bundle\Model\Resource\BundleFactory', ['create'], [], '', false
        );
        $this->optionCollectionFactoryMock = $this->getMock(
            '\Magento\Bundle\Model\Resource\Option\CollectionFactory', ['create'], [], '', false
        );
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface', [], [], '', false);

        $this->model = $helper->getObject(
            '\Magento\Bundle\Model\LinkManagement',
            [
                'productRepository' => $this->productRepository,
                'linkBuilder' => $this->linkBuilder,
                'bundleFactory' => $this->bundleFactoryMock,
                'bundleSelection' => $this->bundleSelectionMock,
                'optionCollection' => $this->optionCollectionFactoryMock,
                'storeManager' => $this->storeManagerMock,
            ]
        );
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

        $this->option->expects($this->any())->method('getSelections')->will($this->returnValue([$this->product]));

        $this->linkBuilder->expects($this->once())->method('populateWithArray')->willReturnSelf();
        $this->linkBuilder->expects($this->once())->method('setIsDefault')->willReturnSelf();
        $this->linkBuilder->expects($this->once())->method('setQty')->willReturnSelf();
        $this->linkBuilder->expects($this->once())->method('setIsDefined')->willReturnSelf();
        $this->linkBuilder->expects($this->once())->method('setPrice')->willReturnSelf();
        $this->linkBuilder->expects($this->once())->method('setPriceType')->willReturnSelf();
        $this->linkBuilder->expects($this->once())->method('create')->willReturn($this->link);

        $this->assertEquals([$this->link], $this->model->getChildren($productSku));
    }

    /**
     * @expectedException \Magento\Webapi\Exception
     * @expectedExceptionCode 403
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
        $productLink = $this->getMock('\Magento\Bundle\Api\Data\LinkInterface');
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
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
        $productLink = $this->getMock('\Magento\Bundle\Api\Data\LinkInterface');
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));
        $productMock->expects($this->once())->method('getId')->will($this->returnValue('product_id'));

        $store = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue(0));

        $option = $this->getMockBuilder('\Magento\Bundle\Model\Option')->disableOriginalConstructor()
            ->setMethods(['getOptionId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getOptionId')->will($this->returnValue(2));

        $optionsCollectionMock = $this->getMock(
            '\Magento\Bundle\Model\Resource\Option\Collection', [], [], '', false
        );
        $optionsCollectionMock->expects($this->once())
            ->method('setProductIdFilter')
            ->with($this->equalTo('product_id'))
            ->will($this->returnSelf());
        $optionsCollectionMock->expects($this->once())
            ->method('joinValues')
            ->with($this->equalTo(0))
            ->will($this->returnSelf());
        $optionsCollectionMock->expects($this->any())->method('getIterator')->will(
            $this->returnValue(new \ArrayIterator([$option]))
        );
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
        $productLink = $this->getMock('\Magento\Bundle\Api\Data\LinkInterface');
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue('linked_product_sku'));
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));
        $productMock->expects($this->any())->method('getId')->will($this->returnValue('product_id'));

        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $linkedProductMock->expects($this->any())->method('getId')->will($this->returnValue(13));
        $linkedProductMock->expects($this->once())->method('isComposite')->will($this->returnValue(true));
        $this->productRepository
            ->expects($this->once())
            ->method('get')
            ->with('linked_product_sku')
            ->will($this->returnValue($linkedProductMock));

        $store = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue(0));

        $option = $this->getMockBuilder('\Magento\Bundle\Model\Option')->disableOriginalConstructor()
            ->setMethods(['getOptionId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getOptionId')->will($this->returnValue(1));

        $optionsCollectionMock = $this->getMock('\Magento\Bundle\Model\Resource\Option\Collection', [], [], '', false);
        $optionsCollectionMock->expects($this->once())
            ->method('setProductIdFilter')
            ->with($this->equalTo('product_id'))
            ->will($this->returnSelf());
        $optionsCollectionMock->expects($this->once())
            ->method('joinValues')
            ->with($this->equalTo(0))
            ->will($this->returnSelf());
        $optionsCollectionMock->expects($this->any())->method('getIterator')->will(
            $this->returnValue(new \ArrayIterator([$option]))
        );
        $this->optionCollectionFactoryMock->expects($this->any())->method('create')->will(
            $this->returnValue($optionsCollectionMock)
        );

        $bundle = $this->getMock('\Magento\Bundle\Model\Resource\Bundle', [], [], '', false);
        $bundle->expects($this->once())->method('getSelectionsData')->with('product_id')->will($this->returnValue([]));
        $this->bundleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($bundle));
        $this->model->addChild($productMock, 1, $productLink);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testAddChildProductAlreadyExistsInOption()
    {
        $productLink = $this->getMock('\Magento\Bundle\Api\Data\LinkInterface');
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue('linked_product_sku'));
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));
        $productMock->expects($this->any())->method('getId')->will($this->returnValue('product_id'));

        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $linkedProductMock->expects($this->any())->method('getId')->will($this->returnValue(13));
        $linkedProductMock->expects($this->once())->method('isComposite')->will($this->returnValue(false));
        $this->productRepository
            ->expects($this->once())
            ->method('get')
            ->with('linked_product_sku')
            ->will($this->returnValue($linkedProductMock));

        $store = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue(0));

        $option = $this->getMockBuilder('\Magento\Bundle\Model\Option')->disableOriginalConstructor()
            ->setMethods(['getOptionId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getOptionId')->will($this->returnValue(1));

        $optionsCollectionMock = $this->getMock('\Magento\Bundle\Model\Resource\Option\Collection', [], [], '', false);
        $optionsCollectionMock->expects($this->once())
            ->method('setProductIdFilter')
            ->with($this->equalTo('product_id'))
            ->will($this->returnSelf());
        $optionsCollectionMock->expects($this->once())
            ->method('joinValues')
            ->with($this->equalTo(0))
            ->will($this->returnSelf());
        $optionsCollectionMock->expects($this->any())->method('getIterator')->will(
            $this->returnValue(new \ArrayIterator([$option]))
        );
        $this->optionCollectionFactoryMock->expects($this->any())->method('create')->will(
            $this->returnValue($optionsCollectionMock)
        );

        $selections = [
            ['option_id' => 1, 'product_id' => 12],
            ['option_id' => 1, 'product_id' => 13],
        ];
        $bundle = $this->getMock('\Magento\Bundle\Model\Resource\Bundle', [], [], '', false);
        $bundle->expects($this->once())->method('getSelectionsData')
            ->with('product_id')
            ->will($this->returnValue($selections));
        $this->bundleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($bundle));
        $this->model->addChild($productMock, 1, $productLink);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testAddChildCouldNotSave()
    {
        $productLink = $this->getMock('\Magento\Bundle\Api\Data\LinkInterface');
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue('linked_product_sku'));
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));
        $productMock->expects($this->any())->method('getId')->will($this->returnValue('product_id'));

        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $linkedProductMock->expects($this->any())->method('getId')->will($this->returnValue(13));
        $linkedProductMock->expects($this->once())->method('isComposite')->will($this->returnValue(false));
        $this->productRepository
            ->expects($this->once())
            ->method('get')
            ->with('linked_product_sku')
            ->will($this->returnValue($linkedProductMock));

        $store = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue(0));

        $option = $this->getMockBuilder('\Magento\Bundle\Model\Option')->disableOriginalConstructor()
            ->setMethods(['getOptionId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getOptionId')->will($this->returnValue(1));

        $optionsCollectionMock = $this->getMock(
            '\Magento\Bundle\Model\Resource\Option\Collection', [], [], '', false
        );
        $optionsCollectionMock->expects($this->once())
            ->method('setProductIdFilter')
            ->with($this->equalTo('product_id'))
            ->will($this->returnSelf());
        $optionsCollectionMock->expects($this->once())
            ->method('joinValues')
            ->with($this->equalTo(0))
            ->will($this->returnSelf());
        $optionsCollectionMock->expects($this->any())->method('getIterator')->will(
            $this->returnValue(new \ArrayIterator([$option]))
        );
        $this->optionCollectionFactoryMock->expects($this->any())->method('create')->will(
            $this->returnValue($optionsCollectionMock)
        );

        $selections = [
            ['option_id' => 1, 'product_id' => 11],
            ['option_id' => 1, 'product_id' => 12],
        ];
        $bundle = $this->getMock('\Magento\Bundle\Model\Resource\Bundle', [], [], '', false);
        $bundle->expects($this->once())->method('getSelectionsData')
            ->with('product_id')
            ->will($this->returnValue($selections));
        $this->bundleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($bundle));

        $selection = $this->getMock('\Magento\Framework\Object', ['save'], [], '', false);
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
        $productLink = $this->getMock('\Magento\Bundle\Api\Data\LinkInterface');
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue('linked_product_sku'));
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));
        $productMock->expects($this->any())->method('getId')->will($this->returnValue('product_id'));

        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $linkedProductMock->expects($this->any())->method('getId')->will($this->returnValue(13));
        $linkedProductMock->expects($this->once())->method('isComposite')->will($this->returnValue(false));
        $this->productRepository
            ->expects($this->once())
            ->method('get')
            ->with('linked_product_sku')
            ->will($this->returnValue($linkedProductMock));

        $store = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue(0));

        $option = $this->getMockBuilder('\Magento\Bundle\Model\Option')->disableOriginalConstructor()
            ->setMethods(['getOptionId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getOptionId')->will($this->returnValue(1));

        $optionsCollectionMock = $this->getMock(
            '\Magento\Bundle\Model\Resource\Option\Collection', [], [], '', false
        );
        $optionsCollectionMock->expects($this->once())
            ->method('setProductIdFilter')
            ->with($this->equalTo('product_id'))
            ->will($this->returnSelf());
        $optionsCollectionMock->expects($this->once())
            ->method('joinValues')
            ->with($this->equalTo(0))
            ->will($this->returnSelf());
        $optionsCollectionMock->expects($this->any())->method('getIterator')->will(
            $this->returnValue(new \ArrayIterator([$option]))
        );
        $this->optionCollectionFactoryMock->expects($this->any())->method('create')->will(
            $this->returnValue($optionsCollectionMock)
        );

        $selections = [
            ['option_id' => 1, 'product_id' => 11],
            ['option_id' => 1, 'product_id' => 12],
        ];
        $bundle = $this->getMock('\Magento\Bundle\Model\Resource\Bundle', [], [], '', false);
        $bundle->expects($this->once())->method('getSelectionsData')
            ->with('product_id')
            ->will($this->returnValue($selections));
        $this->bundleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($bundle));

        $selection = $this->getMock('\Magento\Framework\Object', ['save', 'getId'], [], '', false);
        $selection->expects($this->once())->method('save');
        $selection->expects($this->once())->method('getId')->will($this->returnValue(42));
        $this->bundleSelectionMock->expects($this->once())->method('create')->will($this->returnValue($selection));
        $result = $this->model->addChild($productMock, 1, $productLink);
        $this->assertEquals(42, $result);
    }

    public function testRemoveChild()
    {
        $this->productRepository->expects($this->any())->method('get')->will($this->returnValue($this->product));
        $bundle = $this->getMock('\Magento\Bundle\Model\Resource\Bundle', [], [], '', false);
        $this->bundleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($bundle));
        $productSku = 'productSku';
        $optionId = 1;
        $childSku = 'childSku';

        $this->product
            ->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE));

        $this->getRemoveOptions();

        $selection = $this->getMockBuilder('\Magento\Bundle\Model\Selection')
            ->setMethods(['getSku', 'getOptionId', 'getSelectionId', 'getProductId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selection->expects($this->any())->method('getSku')->will($this->returnValue($childSku));
        $selection->expects($this->any())->method('getOptionId')->will($this->returnValue($optionId));
        $selection->expects($this->any())->method('getSelectionId')->will($this->returnValue(55));
        $selection->expects($this->any())->method('getProductId')->will($this->returnValue(1));

        $this->option->expects($this->any())->method('getSelections')->will($this->returnValue([$selection]));
        $this->product->expects($this->any())->method('getId')->will($this->returnValue(3));

        $bundle->expects($this->once())->method('dropAllUnneededSelections')->with(3, []);
        $bundle->expects($this->once())->method('saveProductRelations')->with(3, []);
        //Params come in lowercase to method
        $this->assertTrue($this->model->removeChild($productSku, $optionId, $childSku));
    }

    /**
     * @expectedException \Magento\Webapi\Exception
     * @expectedExceptionCode 403
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

        $selection = $this->getMockBuilder('\Magento\Bundle\Model\Selection')
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

        $selection = $this->getMockBuilder('\Magento\Bundle\Model\Selection')
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
            ->with($this->equalTo([]))
            ->will($this->returnValue([$this->option]));
    }
}
