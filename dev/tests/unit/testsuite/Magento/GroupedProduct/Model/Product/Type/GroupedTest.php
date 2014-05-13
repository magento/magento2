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
namespace Magento\GroupedProduct\Model\Product\Type;

class GroupedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogProductLink;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productStatusMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectHelper;

    protected function setUp()
    {
        $this->objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', array(), array(), '', false);
        $coreDataMock = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);
        $fileStorageDbMock = $this->getMock('Magento\Core\Helper\File\Storage\Database', array(), array(), '', false);
        $filesystem = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);
        $coreRegistry = $this->getMock('Magento\Framework\Registry', array(), array(), '', false);
        $this->product = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $logger = $this->getMock('Magento\Framework\Logger', array(), array(), '', false);
        $productFactoryMock = $this->getMock('Magento\Catalog\Model\ProductFactory', array(), array(), '', false);
        $this->catalogProductLink = $this->getMock(
            '\Magento\GroupedProduct\Model\Resource\Product\Link',
            array(),
            array(),
            '',
            false
        );
        $this->productStatusMock = $this->getMock(
            '\Magento\Catalog\Model\Product\Attribute\Source\Status',
            array(),
            array(),
            '',
            false
        );
        $this->_model = $this->objectHelper->getObject(
            'Magento\GroupedProduct\Model\Product\Type\Grouped',
            array(
                'eventManager' => $eventManager,
                'coreData' => $coreDataMock,
                'fileStorageDb' => $fileStorageDbMock,
                'filesystem' => $filesystem,
                'coreRegistry' => $coreRegistry,
                'logger' => $logger,
                'productFactory' => $productFactoryMock,
                'catalogProductLink' => $this->catalogProductLink,
                'catalogProductStatus' => $this->productStatusMock
            )
        );
    }

    public function testHasWeightFalse()
    {
        $this->assertFalse($this->_model->hasWeight(), 'This product has weight, but it should not');
    }

    public function testGetChildrenIds()
    {
        $parentId = 12345;
        $childrenIds = array(100, 200, 300);
        $this->catalogProductLink->expects(
            $this->once()
        )->method(
            'getChildrenIds'
        )->with(
            $parentId,
            \Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED
        )->will(
            $this->returnValue($childrenIds)
        );
        $this->assertEquals($childrenIds, $this->_model->getChildrenIds($parentId));
    }

    public function testGetParentIdsByChild()
    {
        $childId = 12345;
        $parentIds = array(100, 200, 300);
        $this->catalogProductLink->expects(
            $this->once()
        )->method(
            'getParentIdsByChild'
        )->with(
            $childId,
            \Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED
        )->will(
            $this->returnValue($parentIds)
        );
        $this->assertEquals($parentIds, $this->_model->getParentIdsByChild($childId));
    }

    public function testGetAssociatedProducts()
    {
        $cached = true;
        $associatedProducts = array(5, 7, 11, 13, 17);
        $this->product->expects($this->once())->method('hasData')->will($this->returnValue($cached));
        $this->product->expects($this->once())->method('getData')->will($this->returnValue($associatedProducts));
        $this->assertEquals($associatedProducts, $this->_model->getAssociatedProducts($this->product));
    }

    /**
     * @param int $status
     * @param array $filters
     * @param array $result
     * @dataProvider addStatusFilterDataProvider
     */
    public function testAddStatusFilter($status, $filters, $result)
    {
        $this->product->expects($this->once())->method('getData')->will($this->returnValue($filters));
        $this->product->expects($this->once())->method('setData')->with('_cache_instance_status_filters', $result);
        $this->assertEquals($this->_model, $this->_model->addStatusFilter($status, $this->product));
    }

    /**
     * @return array
     */
    public function addStatusFilterDataProvider()
    {
        return array(array(1, array(), array(1)), array(1, false, array(1)));
    }

    public function testSetSaleableStatus()
    {
        $key = '_cache_instance_status_filters';
        $saleableIds = array(300, 800, 500);

        $this->productStatusMock->expects(
            $this->once()
        )->method(
            'getSaleableStatusIds'
        )->will(
            $this->returnValue($saleableIds)
        );
        $this->product->expects($this->once())->method('setData')->with($key, $saleableIds);
        $this->assertEquals($this->_model, $this->_model->setSaleableStatus($this->product));
    }

    public function testGetStatusFiltersNoData()
    {
        $result = array(
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED
        );
        $this->product->expects($this->once())->method('hasData')->will($this->returnValue(false));
        $this->assertEquals($result, $this->_model->getStatusFilters($this->product));
    }

    public function testGetStatusFiltersWithData()
    {
        $result = array(
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED
        );
        $this->product->expects($this->once())->method('hasData')->will($this->returnValue(true));
        $this->product->expects($this->once())->method('getData')->will($this->returnValue($result));
        $this->assertEquals($result, $this->_model->getStatusFilters($this->product));
    }

    public function testGetAssociatedProductIdsCached()
    {
        $key = '_cache_instance_associated_product_ids';
        $cachedData = array(300, 303, 306);

        $this->product->expects($this->once())->method('hasData')->with($key)->will($this->returnValue(true));
        $this->product->expects($this->never())->method('setData');
        $this->product->expects($this->once())->method('getData')->with($key)->will($this->returnValue($cachedData));

        $this->assertEquals($cachedData, $this->_model->getAssociatedProductIds($this->product));
    }

    public function testGetAssociatedProductIdsNonCached()
    {
        $args = $this->objectHelper->getConstructArguments(
            '\Magento\GroupedProduct\Model\Product\Type\Grouped',
            array()
        );

        /** @var \Magento\GroupedProduct\Model\Product\Type\Grouped $model */
        $model = $this->getMock(
            '\Magento\GroupedProduct\Model\Product\Type\Grouped',
            array('getAssociatedProducts'),
            $args
        );

        $associatedProduct = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $model->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->product
        )->will(
            $this->returnValue(array($associatedProduct))
        );

        $associatedId = 9384;
        $key = '_cache_instance_associated_product_ids';
        $associatedIds = array($associatedId);
        $associatedProduct->expects($this->once())->method('getId')->will($this->returnValue($associatedId));

        $this->product->expects($this->once())->method('hasData')->with($key)->will($this->returnValue(false));
        $this->product->expects($this->once())->method('setData')->with($key, $associatedIds);
        $this->product->expects(
            $this->once()
        )->method(
            'getData'
        )->with(
            $key
        )->will(
            $this->returnValue($associatedIds)
        );

        $this->assertEquals($associatedIds, $model->getAssociatedProductIds($this->product));
    }

    public function testGetAssociatedProductCollection()
    {
        $link = $this->getMock('Magento\Catalog\Model\Product\Link', array(), array(), '', false);
        $this->product->expects($this->once())->method('getLinkInstance')->will($this->returnValue($link));
        $link->expects(
            $this->any()
        )->method(
            'setLinkTypeId'
        )->with(
            \Magento\GroupedProduct\Model\Resource\Product\Link::LINK_TYPE_GROUPED
        );
        $collection = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Link\Product\Collection',
            array('setFlag', 'setIsStrongMode', 'setProduct'),
            array(),
            '',
            false
        );
        $link->expects($this->once())->method('getProductCollection')->will($this->returnValue($collection));
        $collection->expects($this->any())->method('setFlag')->will($this->returnValue($collection));
        $collection->expects($this->once())->method('setIsStrongMode')->will($this->returnValue($collection));
        $this->assertEquals($collection, $this->_model->getAssociatedProductCollection($this->product));
    }

    /**
     * @param array $superGroup
     * @param array $result
     * @dataProvider processBuyRequestDataProvider
     */
    public function testProcessBuyRequest($superGroup, $result)
    {
        $buyRequest = $this->getMock('\Magento\Framework\Object', array('getSuperGroup'), array(), '', false);
        $buyRequest->expects($this->any())->method('getSuperGroup')->will($this->returnValue($superGroup));

        $this->assertEquals($result, $this->_model->processBuyRequest($this->product, $buyRequest));
    }

    /**
     * @return array
     */
    public function processBuyRequestDataProvider()
    {
        return array(
            'positive' => array(array(1, 2, 3), array('super_group' => array(1, 2, 3))),
            'negative' => array(false, array('super_group' => array()))
        );
    }
}
