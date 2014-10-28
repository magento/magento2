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
namespace Magento\Bundle\Model\Product;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Model\Product\Type
     */
    protected $model;

    /**
     * @var \Magento\Bundle\Model\Resource\Selection\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bundleCollection;

    /**
     * @var \Magento\Catalog\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogData;

    /**
     * @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Bundle\Model\OptionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bundleOption;

    protected function setUp()
    {
        $this->bundleCollection = $this->getMockBuilder('Magento\Bundle\Model\Resource\Selection\CollectionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogData = $this->getMockBuilder('Magento\Catalog\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder('Magento\Framework\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->bundleOption = $this->getMockBuilder('Magento\Bundle\Model\OptionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectHelper->getObject(
            'Magento\Bundle\Model\Product\Type',
            array(
                'productFactory' => $this->getMock('Magento\Catalog\Model\ProductFactory'),
                'bundleModelSelection' => $this->getMock('Magento\Bundle\Model\SelectionFactory'),
                'bundleFactory' => $this->getMock('Magento\Bundle\Model\Resource\BundleFactory'),
                'bundleCollection' => $this->bundleCollection,
                'bundleOption' => $this->bundleOption,
                'catalogData' => $this->catalogData,
                'storeManager' => $this->storeManager
            )
        );
    }

    public function testHasWeightTrue()
    {
        $this->assertTrue($this->model->hasWeight(), 'This product has not weight, but it should');
    }

    public function testGetIdentities()
    {
        $identities = array('id1', 'id2');
        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $optionMock = $this->getMock(
            '\Magento\Bundle\Model\Option',
            array('getSelections', '__wakeup'),
            array(),
            '',
            false
        );
        $optionCollectionMock = $this->getMock(
            'Magento\Bundle\Model\Resource\Option\Collection',
            array(),
            array(),
            '',
            false
        );
        $cacheKey = '_cache_instance_options_collection';
        $productMock->expects($this->once())
            ->method('getIdentities')
            ->will($this->returnValue($identities));
        $productMock->expects($this->once())
            ->method('hasData')
            ->with($cacheKey)
            ->will($this->returnValue(true));
        $productMock->expects($this->once())
            ->method('getData')
            ->with($cacheKey)
            ->will($this->returnValue($optionCollectionMock));
        $optionCollectionMock
            ->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue(array($optionMock)));
        $optionMock
            ->expects($this->exactly(2))
            ->method('getSelections')
            ->will($this->returnValue(array($productMock)));
        $this->assertEquals($identities, $this->model->getIdentities($productMock));
    }

    public function testGetSkuWithType()
    {
        $sku = 'sku';
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->at(0))
            ->method('getData')
            ->with('sku')
            ->will($this->returnValue($sku));
        $productMock->expects($this->at(2))
            ->method('getData')
            ->with('sku_type')
            ->will($this->returnValue('some_data'));

        $this->assertEquals($sku, $this->model->getSku($productMock));
    }

    public function testGetSkuWithoutType()
    {
        $sku = 'sku';
        $itemSku = 'item';
        $selectionIds = [1, 2, 3];
        $serializeIds = serialize($selectionIds);
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'getData', 'hasCustomOptions', 'getCustomOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $customOptionMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Configuration\Item\Option')
            ->setMethods(['getValue', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selectionItemMock = $this->getMockBuilder('Magento\Framework\Object')
            ->setMethods(['getSku', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->at(0))
            ->method('getData')
            ->with('sku')
            ->will($this->returnValue($sku));
        $productMock->expects($this->at(1))
            ->method('getCustomOption')
            ->with('option_ids')
            ->will($this->returnValue(false));
        $productMock->expects($this->at(2))
            ->method('getData')
            ->with('sku_type')
            ->will($this->returnValue(null));
        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->will($this->returnValue(true));
        $productMock->expects($this->at(4))
            ->method('getCustomOption')
            ->with('bundle_selection_ids')
            ->will($this->returnValue($customOptionMock));
        $customOptionMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($serializeIds));
        $selectionMock = $this->getSelectionsByIdsMock($selectionIds, $productMock, 5, 6);
        $selectionMock->expects(($this->any()))
            ->method('getItems')
            ->will($this->returnValue(array($selectionItemMock)));
        $selectionItemMock->expects($this->any())
            ->method('getSku')
            ->will($this->returnValue($itemSku));

        $this->assertEquals($sku . '-' . $itemSku, $this->model->getSku($productMock));
    }

    public function testGetWeightWithoutCustomOption()
    {
        $weight = 5;
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->at(0))
            ->method('getData')
            ->with('weight_type')
            ->will($this->returnValue(true));
        $productMock->expects($this->at(1))
            ->method('getData')
            ->with('weight')
            ->will($this->returnValue($weight));

        $this->assertEquals($weight, $this->model->getWeight($productMock));
    }

    public function testGetWeightWithCustomOption()
    {
        $weight = 5;
        $selectionIds = [1, 2, 3];
        $serializeIds = serialize($selectionIds);
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'getData', 'hasCustomOptions', 'getCustomOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $customOptionMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Configuration\Item\Option')
            ->setMethods(['getValue', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selectionItemMock = $this->getMockBuilder('Magento\Framework\Object')
            ->setMethods(['getSelectionId', 'getWeight', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->at(0))
            ->method('getData')
            ->with('weight_type')
            ->will($this->returnValue(false));
        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->will($this->returnValue(true));
        $productMock->expects($this->at(2))
            ->method('getCustomOption')
            ->with('bundle_selection_ids')
            ->will($this->returnValue($customOptionMock));
        $customOptionMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($serializeIds));
        $selectionMock = $this->getSelectionsByIdsMock($selectionIds, $productMock, 3, 4);
        $selectionMock->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue(array($selectionItemMock)));
        $selectionItemMock->expects($this->any())
            ->method('getSelectionId')
            ->will($this->returnValue('id'));
        $productMock->expects($this->at(5))
            ->method('getCustomOption')
            ->with('selection_qty_' . 'id')
            ->will($this->returnValue(null));
        $selectionItemMock->expects($this->once())
            ->method('getWeight')
            ->will($this->returnValue($weight));


        $this->assertEquals($weight, $this->model->getWeight($productMock));
    }

    public function testGetWeightWithSeveralCustomOption()
    {
        $weight = 5;
        $qtyOption = 5;
        $selectionIds = [1, 2, 3];
        $serializeIds = serialize($selectionIds);
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'getData', 'hasCustomOptions', 'getCustomOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $customOptionMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Configuration\Item\Option')
            ->setMethods(['getValue', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $qtyOptionMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Configuration\Item\Option')
            ->setMethods(['getValue', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selectionItemMock = $this->getMockBuilder('Magento\Framework\Object')
            ->setMethods(['getSelectionId', 'getWeight', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->at(0))
            ->method('getData')
            ->with('weight_type')
            ->will($this->returnValue(false));
        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->will($this->returnValue(true));
        $productMock->expects($this->at(2))
            ->method('getCustomOption')
            ->with('bundle_selection_ids')
            ->will($this->returnValue($customOptionMock));
        $customOptionMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($serializeIds));
        $selectionMock = $this->getSelectionsByIdsMock($selectionIds, $productMock, 3, 4);
        $selectionMock->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue(array($selectionItemMock)));
        $selectionItemMock->expects($this->any())
            ->method('getSelectionId')
            ->will($this->returnValue('id'));
        $productMock->expects($this->at(5))
            ->method('getCustomOption')
            ->with('selection_qty_' . 'id')
            ->will($this->returnValue($qtyOptionMock));
        $qtyOptionMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($qtyOption));
        $selectionItemMock->expects($this->once())
            ->method('getWeight')
            ->will($this->returnValue($weight));

        $this->assertEquals($weight * $qtyOption, $this->model->getWeight($productMock));
    }

    public function testIsVirtualWithoutCustomOption()
    {
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->will($this->returnValue(false));

        $this->assertFalse($this->model->isVirtual($productMock));
    }

    public function testIsVirtual()
    {
        $selectionIds = [1, 2, 3];
        $serializeIds = serialize($selectionIds);

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $customOptionMock = $this->getMockBuilder('Magento\Catalog\Model\Product\Configuration\Item\Option')
            ->setMethods(['getValue', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selectionItemMock = $this->getMockBuilder('Magento\Framework\Object')
            ->setMethods(['isVirtual', 'getItems', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->will($this->returnValue(true));
        $productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('bundle_selection_ids')
            ->will($this->returnValue($customOptionMock));
        $customOptionMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($serializeIds));
        $selectionMock = $this->getSelectionsByIdsMock($selectionIds, $productMock, 2, 3);
        $selectionMock->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue(array($selectionItemMock)));
        $selectionItemMock->expects($this->once())
            ->method('isVirtual')
            ->will($this->returnValue(true));
        $selectionItemMock->expects($this->once())
            ->method('isVirtual')
            ->will($this->returnValue(true));
        $selectionMock->expects($this->once())
            ->method('count')
            ->will($this->returnValue(1));

        $this->assertTrue($this->model->isVirtual($productMock));
    }

    /**
     * @param $selectionIds
     * @param $productMock
     * @param $getSelectionsIndex
     * @param $getSelectionsIdsIndex
     * @return \PHPUnit_Framework_MockObject_MockObject
     */

    protected function getSelectionsByIdsMock ($selectionIds, $productMock, $getSelectionsIndex, $getSelectionsIdsIndex)
    {
        $usedSelectionsMock = $this->getMockBuilder('Magento\Bundle\Model\Resource\Selection\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->at($getSelectionsIndex))
            ->method('getData')
            ->with('_cache_instance_used_selections')
            ->will($this->returnValue($usedSelectionsMock));
        $productMock->expects($this->at($getSelectionsIdsIndex))
            ->method('getData')
            ->with('_cache_instance_used_selections_ids')
            ->will($this->returnValue($selectionIds));

        return $usedSelectionsMock;
    }

    /**
     * @param $expected
     * @param $firstId
     * @param $secondId
     * @dataProvider shakeSelectionsDataProvider
     */
    public function testShakeSelections($expected, $firstId, $secondId)
    {
        $firstItemMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'getOption', 'getOptionId', 'getPosition', 'getSelectionId'])
            ->disableOriginalConstructor()
            ->getMock();
        $secondItemMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods(['__wakeup', 'getOption', 'getOptionId', 'getPosition', 'getSelectionId'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionFirstMock = $this->getMockBuilder('Magento\Bundle\Model\Option')
            ->setMethods(['getPosition', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionSecondMock = $this->getMockBuilder('Magento\Bundle\Model\Option')
            ->setMethods(['getPosition', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $firstItemMock->expects($this->once())
            ->method('getOption')
            ->will($this->returnValue($optionFirstMock));
        $optionFirstMock->expects($this->once())
            ->method('getPosition')
            ->will($this->returnValue('option_position'));
        $firstItemMock->expects($this->once())
            ->method('getOptionId')
            ->will($this->returnValue('option_id'));
        $firstItemMock->expects($this->once())
            ->method('getPosition')
            ->will($this->returnValue('position'));
        $firstItemMock->expects($this->once())
            ->method('getSelectionId')
            ->will($this->returnValue($firstId));
        $secondItemMock->expects($this->once())
            ->method('getOption')
            ->will($this->returnValue($optionSecondMock));
        $optionSecondMock->expects($this->any())
            ->method('getPosition')
            ->will($this->returnValue('option_position'));
        $secondItemMock->expects($this->once())
            ->method('getOptionId')
            ->will($this->returnValue('option_id'));
        $secondItemMock->expects($this->once())
            ->method('getPosition')
            ->will($this->returnValue('position'));
        $secondItemMock->expects($this->once())
            ->method('getSelectionId')
            ->will($this->returnValue($secondId));

        $this->assertEquals($expected, $this->model->shakeSelections($firstItemMock, $secondItemMock));
    }

    /**
     * @return array
     */
    public function shakeSelectionsDataProvider()
    {
        return [
            [0, 0, 0],
            [1, 1, 0],
            [-1, 0, 1]
        ];
    }

    public function testGetSelectionsByIds()
    {
        $selectionIds = [1, 2, 3];
        $usedSelectionsIds =[4, 5, 6];
        $storeId = 2;
        $websiteId = 1;
        $storeFilter = 'store_filter';
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $usedSelectionsMock = $this->getMockBuilder('Magento\Bundle\Model\Resource\Selection\Collection')
            ->setMethods([
                    'addAttributeToSelect',
                    'setFlag',
                    'addStoreFilter',
                    'setStoreId',
                    'setPositionOrder',
                    'addFilterByRequiredOptions',
                    'setSelectionIdsFilter',
                    'joinPrices'
                ])
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->setMethods(['getWebsiteId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->at(0))
            ->method('getData')
            ->with('_cache_instance_used_selections')
            ->will($this->returnValue(null));
        $productMock->expects($this->at(1))
            ->method('getData')
            ->with('_cache_instance_used_selections_ids')
            ->will($this->returnValue($usedSelectionsIds));
        $productMock->expects($this->once())
            ->method('getStoreId')
            ->will($this->returnValue($storeId));
        $this->bundleCollection->expects($this->once())
            ->method('create')
            ->will($this->returnValue($usedSelectionsMock));
        $usedSelectionsMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with('*')
            ->will($this->returnSelf());
        $usedSelectionsMock->expects($this->at(1))
            ->method('setFlag')
            ->with('require_stock_items', true)
            ->will($this->returnSelf());
        $usedSelectionsMock->expects($this->at(2))
            ->method('setFlag')
            ->with('product_children', true)
            ->will($this->returnSelf());
        $productMock->expects($this->at(3))
            ->method('getData')
            ->with('_cache_instance_store_filter')
            ->will($this->returnValue($storeFilter));
        $usedSelectionsMock->expects($this->once())
            ->method('addStoreFilter')
            ->with($storeFilter)
            ->will($this->returnSelf());
        $usedSelectionsMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->will($this->returnSelf());
        $usedSelectionsMock->expects($this->once())
            ->method('setPositionOrder')
            ->will($this->returnSelf());
        $usedSelectionsMock->expects($this->once())
            ->method('addFilterByRequiredOptions')
            ->will($this->returnSelf());
        $usedSelectionsMock->expects($this->once())
            ->method('setSelectionIdsFilter')
            ->with($selectionIds)
            ->will($this->returnSelf());
        $this->catalogData->expects($this->once())
            ->method('isPriceGlobal')
            ->will($this->returnValue(false));
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->will($this->returnValue($storeMock));
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->will($this->returnValue($websiteId));
        $usedSelectionsMock->expects($this->once())
            ->method('joinPrices')
            ->with($websiteId)
            ->will($this->returnSelf());
        $productMock->expects($this->at(4))
            ->method('setData')
            ->with('_cache_instance_used_selections', $usedSelectionsMock)
            ->will($this->returnSelf());
        $productMock->expects($this->at(5))
            ->method('setData')
            ->with('_cache_instance_used_selections_ids', $selectionIds)
            ->will($this->returnSelf());

        $this->model->getSelectionsByIds($selectionIds, $productMock);
    }

    public function testGetOptionsByIds()
    {
        $optionsIds = [1, 2, 3];
        $usedOptionsIds =[4, 5, 6];
        $productId = 3;
        $storeId = 2;
        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $usedOptionsMock = $this->getMockBuilder('Magento\Bundle\Model\Resource\Option\Collection')
            ->setMethods(['getResourceCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $dbResourceMock= $this->getMockBuilder('Magento\Framework\Model\Resource\Db\Collection\AbstractCollection')
            ->setMethods(['setProductIdFilter', 'setPositionOrder', 'joinValues', 'setIdFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->setMethods(['getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->at(0))
            ->method('getData')
            ->with('_cache_instance_used_options')
            ->will($this->returnValue(null));
        $productMock->expects($this->at(1))
            ->method('getData')
            ->with('_cache_instance_used_options_ids')
            ->will($this->returnValue($usedOptionsIds));
        $productMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($productId));
        $this->bundleOption->expects($this->once())
            ->method('create')
            ->will($this->returnValue($usedOptionsMock));
        $usedOptionsMock->expects($this->once())
            ->method('getResourceCollection')
            ->will($this->returnValue($dbResourceMock));
        $dbResourceMock->expects($this->once())
            ->method('setProductIdFilter')
            ->with($productId)
            ->will($this->returnSelf());
        $dbResourceMock->expects($this->once())
            ->method('setPositionOrder')
            ->will($this->returnSelf());
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($storeMock));
        $storeMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($storeId));
        $dbResourceMock->expects($this->once())
            ->method('joinValues')
            ->will($this->returnSelf());
        $dbResourceMock->expects($this->once())
            ->method('setIdFilter')
            ->with($optionsIds)
            ->will($this->returnSelf());
        $productMock->expects($this->at(3))
            ->method('setData')
            ->with('_cache_instance_used_options', $dbResourceMock)
            ->will($this->returnSelf());
        $productMock->expects($this->at(4))
            ->method('setData')
            ->with('_cache_instance_used_options_ids', $optionsIds)
            ->will($this->returnSelf());

        $this->model->getOptionsByIds($optionsIds, $productMock);
    }
}
