<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Bundle\Service\V1\Product\Link;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WriteService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepositoryMock;

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
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var \Magento\Bundle\Model\Product\Type\Interceptor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productType;

    /**
     * @var \Magento\Bundle\Model\Resource\Option\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionCollection;

    /**
     * @var \Magento\Bundle\Model\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    private $option;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->productRepositoryMock = $this->getMock(
            '\Magento\Catalog\Model\ProductRepository', array(), array(), '', false
        );

        $this->bundleSelectionMock = $this->getMock(
            '\Magento\Bundle\Model\SelectionFactory', array('create'), array(), '', false
        );

        $this->bundleFactoryMock = $this->getMock(
            '\Magento\Bundle\Model\Resource\BundleFactory', array('create'), array(), '', false
        );

        $this->optionCollectionFactoryMock = $this->getMock(
            '\Magento\Bundle\Model\Resource\Option\CollectionFactory', array('create'), array(), '', false
        );

        $this->storeManagerMock = $this->getMock(
            '\Magento\Framework\StoreManagerInterface', array(), array(), '', false
        );

        $this->product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['getTypeInstance', 'getStoreId', 'getTypeId', 'getId', '__wakeup'])
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

        $this->service = $helper->getObject('Magento\Bundle\Service\V1\Product\Link\WriteService',
            [
                'productRepository' => $this->productRepositoryMock,
                'bundleSelection' => $this->bundleSelectionMock,
                'bundleFactory' => $this->bundleFactoryMock,
                'optionCollection' => $this->optionCollectionFactoryMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testAddChildToNotBundleProduct()
    {
        $productLink = $this->getMock(
            'Magento\Bundle\Service\V1\Data\Product\Link', array(), array(), '', false
        );
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
        ));
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with('product_sku')
            ->will($this->returnValue($productMock));
        $this->service->addChild('product_sku', 1, $productLink);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testAddChildNonExistingOption()
    {
        $productLink = $this->getMock(
            'Magento\Bundle\Service\V1\Data\Product\Link', array(), array(), '', false
        );
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));
        $productMock->expects($this->once())->method('getId')->will($this->returnValue('product_id'));
        $this->productRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with('product_sku')
            ->will($this->returnValue($productMock));

        $store = $this->getMock('\Magento\Store\Model\Store', array(), array(), '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue(0));

        $option = $this->getMockBuilder('\Magento\Bundle\Model\Option')->disableOriginalConstructor()
            ->setMethods(['getOptionId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getOptionId')->will($this->returnValue(2));

        $optionsCollectionMock = $this->getMock(
            '\Magento\Bundle\Model\Resource\Option\Collection', array(), array(), '', false
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
        $this->service->addChild('product_sku', 1, $productLink);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Bundle product could not contain another composite product
     */
    public function testAddChildLinkedProductIsComposite()
    {
        $productLink = $this->getMock(
            'Magento\Bundle\Service\V1\Data\Product\Link', array(), array(), '', false
        );
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue('linked_product_sku'));
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));
        $productMock->expects($this->any())->method('getId')->will($this->returnValue('product_id'));

        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false);
        $linkedProductMock->expects($this->any())->method('getId')->will($this->returnValue(13));
        $linkedProductMock->expects($this->once())->method('isComposite')->will($this->returnValue(true));
        $this->productRepositoryMock
            ->expects($this->at(0))
            ->method('get')
            ->with('product_sku')
            ->will($this->returnValue($productMock));
        $this->productRepositoryMock
            ->expects($this->at(1))
            ->method('get')
            ->with('linked_product_sku')
            ->will($this->returnValue($linkedProductMock));

        $store = $this->getMock('\Magento\Store\Model\Store', array(), array(), '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue(0));

        $option = $this->getMockBuilder('\Magento\Bundle\Model\Option')->disableOriginalConstructor()
            ->setMethods(['getOptionId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getOptionId')->will($this->returnValue(1));

        $optionsCollectionMock = $this->getMock(
            '\Magento\Bundle\Model\Resource\Option\Collection', array(), array(), '', false
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

        $bundle = $this->getMock('\Magento\Bundle\Model\Resource\Bundle', array(), array(), '', false);
        $bundle->expects($this->once())->method('getSelectionsData')
            ->with('product_id')
            ->will($this->returnValue([]));
        $this->bundleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($bundle));

        $this->service->addChild('product_sku', 1, $productLink);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testAddChildProductAlreadyExistsInOption()
    {
        $productLink = $this->getMock(
            'Magento\Bundle\Service\V1\Data\Product\Link', array(), array(), '', false
        );
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue('linked_product_sku'));
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));
        $productMock->expects($this->any())->method('getId')->will($this->returnValue('product_id'));

        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false);
        $linkedProductMock->expects($this->any())->method('getId')->will($this->returnValue(13));
        $linkedProductMock->expects($this->once())->method('isComposite')->will($this->returnValue(false));
        $this->productRepositoryMock
            ->expects($this->at(0))
            ->method('get')
            ->with('product_sku')
            ->will($this->returnValue($productMock));
        $this->productRepositoryMock
            ->expects($this->at(1))
            ->method('get')
            ->with('linked_product_sku')
            ->will($this->returnValue($linkedProductMock));

        $store = $this->getMock('\Magento\Store\Model\Store', array(), array(), '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue(0));

        $option = $this->getMockBuilder('\Magento\Bundle\Model\Option')->disableOriginalConstructor()
            ->setMethods(['getOptionId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getOptionId')->will($this->returnValue(1));

        $optionsCollectionMock = $this->getMock(
            '\Magento\Bundle\Model\Resource\Option\Collection', array(), array(), '', false
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
            ['option_id' => 1, 'product_id' => 12],
            ['option_id' => 1, 'product_id' => 13],
        ];
        $bundle = $this->getMock('\Magento\Bundle\Model\Resource\Bundle', array(), array(), '', false);
        $bundle->expects($this->once())->method('getSelectionsData')
            ->with('product_id')
            ->will($this->returnValue($selections));
        $this->bundleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($bundle));

        $this->service->addChild('product_sku', 1, $productLink);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testAddChildCouldNotSave()
    {
        $productLink = $this->getMock(
            'Magento\Bundle\Service\V1\Data\Product\Link', array(), array(), '', false
        );
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue('linked_product_sku'));
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));
        $productMock->expects($this->any())->method('getId')->will($this->returnValue('product_id'));

        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false);
        $linkedProductMock->expects($this->any())->method('getId')->will($this->returnValue(13));
        $linkedProductMock->expects($this->once())->method('isComposite')->will($this->returnValue(false));
        $this->productRepositoryMock
            ->expects($this->at(0))
            ->method('get')
            ->with('product_sku')
            ->will($this->returnValue($productMock));
        $this->productRepositoryMock
            ->expects($this->at(1))
            ->method('get')
            ->with('linked_product_sku')
            ->will($this->returnValue($linkedProductMock));

        $store = $this->getMock('\Magento\Store\Model\Store', array(), array(), '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue(0));

        $option = $this->getMockBuilder('\Magento\Bundle\Model\Option')->disableOriginalConstructor()
            ->setMethods(['getOptionId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getOptionId')->will($this->returnValue(1));

        $optionsCollectionMock = $this->getMock(
            '\Magento\Bundle\Model\Resource\Option\Collection', array(), array(), '', false
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
        $bundle = $this->getMock('\Magento\Bundle\Model\Resource\Bundle', array(), array(), '', false);
        $bundle->expects($this->once())->method('getSelectionsData')
            ->with('product_id')
            ->will($this->returnValue($selections));
        $this->bundleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($bundle));

        $selection = $this->getMock('\Magento\Framework\Object', array('save'), array(), '', false);
        $selection->expects($this->once())->method('save')
            ->will(
                $this->returnCallback(
                    function () {
                        throw new \Exception('message');
                    }
                )
            );
        $this->bundleSelectionMock->expects($this->once())->method('create')->will($this->returnValue($selection));
        $this->service->addChild('product_sku', 1, $productLink);
    }

    public function testAddChild()
    {
        $productLink = $this->getMock(
            'Magento\Bundle\Service\V1\Data\Product\Link', array(), array(), '', false
        );
        $productLink->expects($this->any())->method('getSku')->will($this->returnValue('linked_product_sku'));
        $productLink->expects($this->any())->method('getOptionId')->will($this->returnValue(1));

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false);
        $productMock->expects($this->once())->method('getTypeId')->will($this->returnValue(
            \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
        ));
        $productMock->expects($this->any())->method('getId')->will($this->returnValue('product_id'));

        $linkedProductMock = $this->getMock('\Magento\Catalog\Model\Product', array(), array(), '', false);
        $linkedProductMock->expects($this->any())->method('getId')->will($this->returnValue(13));
        $linkedProductMock->expects($this->once())->method('isComposite')->will($this->returnValue(false));
        $this->productRepositoryMock
            ->expects($this->at(0))
            ->method('get')
            ->with('product_sku')
            ->will($this->returnValue($productMock));
        $this->productRepositoryMock
            ->expects($this->at(1))
            ->method('get')
            ->with('linked_product_sku')
            ->will($this->returnValue($linkedProductMock));

        $store = $this->getMock('\Magento\Store\Model\Store', array(), array(), '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $store->expects($this->any())->method('getId')->will($this->returnValue(0));

        $option = $this->getMockBuilder('\Magento\Bundle\Model\Option')->disableOriginalConstructor()
            ->setMethods(['getOptionId', '__wakeup'])
            ->getMock();
        $option->expects($this->once())->method('getOptionId')->will($this->returnValue(1));

        $optionsCollectionMock = $this->getMock(
            '\Magento\Bundle\Model\Resource\Option\Collection', array(), array(), '', false
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
        $bundle = $this->getMock('\Magento\Bundle\Model\Resource\Bundle', array(), array(), '', false);
        $bundle->expects($this->once())->method('getSelectionsData')
            ->with('product_id')
            ->will($this->returnValue($selections));
        $this->bundleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($bundle));

        $selection = $this->getMock('\Magento\Framework\Object', array('save', 'getId'), array(), '', false);
        $selection->expects($this->once())->method('save');
        $selection->expects($this->once())->method('getId')->will($this->returnValue(42));
        $this->bundleSelectionMock->expects($this->once())->method('create')->will($this->returnValue($selection));
        $result = $this->service->addChild('product_sku', 1, $productLink);
        $this->assertEquals(42, $result);
    }


    public function testRemoveChild()
    {
        $this->productRepositoryMock->expects($this->any())->method('get')->will($this->returnValue($this->product));
        $bundle = $this->getMock('\Magento\Bundle\Model\Resource\Bundle', array(), array(), '', false);
        $this->bundleFactoryMock->expects($this->once())->method('create')->will($this->returnValue($bundle));
        $productSku = 'productSku';
        $optionId = 1;
        $childSku = 'childSku';

        $this->product
            ->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE));

        $this->getOptions();

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

        $bundle->expects($this->once())->method('dropAllUnneededSelections')->with(3, array());
        $bundle->expects($this->once())->method('saveProductRelations')->with(3, array());
        //Params come in lowercase to method
        $this->assertTrue($this->service->removeChild($productSku, $optionId, $childSku));
    }

    /**
     * @expectedException \Magento\Webapi\Exception
     * @expectedExceptionCode 403
     */
    public function testRemoveChildForbidden()
    {
        $this->productRepositoryMock->expects($this->any())->method('get')->will($this->returnValue($this->product));
        $productSku = 'productSku';
        $optionId = 1;
        $childSku = 'childSku';

        $this->product
            ->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE));

        $this->service->removeChild($productSku, $optionId, $childSku);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRemoveChildInvalidOptionId()
    {
        $this->productRepositoryMock->expects($this->any())->method('get')->will($this->returnValue($this->product));
        $productSku = 'productSku';
        $optionId = 1;
        $childSku = 'childSku';

        $this->product
            ->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE));

        $this->getOptions();

        $selection = $this->getMockBuilder('\Magento\Bundle\Model\Selection')
            ->setMethods(['getSku', 'getOptionId', 'getSelectionId', 'getProductId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selection->expects($this->any())->method('getSku')->will($this->returnValue($childSku));
        $selection->expects($this->any())->method('getOptionId')->will($this->returnValue($optionId + 1));
        $selection->expects($this->any())->method('getSelectionId')->will($this->returnValue(55));
        $selection->expects($this->any())->method('getProductId')->will($this->returnValue(1));

        $this->option->expects($this->any())->method('getSelections')->will($this->returnValue([$selection]));
        $this->service->removeChild($productSku, $optionId, $childSku);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRemoveChildInvalidChildSku()
    {
        $this->productRepositoryMock->expects($this->any())->method('get')->will($this->returnValue($this->product));
        $productSku = 'productSku';
        $optionId = 1;
        $childSku = 'childSku';

        $this->product
            ->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE));

        $this->getOptions();

        $selection = $this->getMockBuilder('\Magento\Bundle\Model\Selection')
            ->setMethods(['getSku', 'getOptionId', 'getSelectionId', 'getProductId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selection->expects($this->any())->method('getSku')->will($this->returnValue($childSku . '_invalid'));
        $selection->expects($this->any())->method('getOptionId')->will($this->returnValue($optionId));
        $selection->expects($this->any())->method('getSelectionId')->will($this->returnValue(55));
        $selection->expects($this->any())->method('getProductId')->will($this->returnValue(1));

        $this->option->expects($this->any())->method('getSelections')->will($this->returnValue([$selection]));
        $this->service->removeChild($productSku, $optionId, $childSku);
    }

    public function getOptions()
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
