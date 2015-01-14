<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for \Magento\CatalogSearch\Model\Search\IndexBuilder
 */
class IndexBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|MockObject */
    private $adapter;

    /** @var \Magento\Framework\DB\Select|MockObject */
    private $select;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|MockObject */
    private $config;

    /** @var \Magento\Store\Model\StoreManagerInterface|MockObject */
    private $storeManager;

    /** @var \Magento\Framework\Search\RequestInterface|MockObject */
    private $request;

    /** @var \Magento\Framework\App\Resource|MockObject */
    private $resource;

    /**
     * @var \Magento\CatalogSearch\Model\Search\IndexBuilder
     */
    private $target;

    protected function setUp()
    {
        $this->select = $this->getMockBuilder('\Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->setMethods(['from', 'joinLeft', 'where'])
            ->getMock();

        $this->adapter = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->setMethods(['select', 'quoteInto'])
            ->getMockForAbstractClass();
        $this->adapter->expects($this->once())
            ->method('select')
            ->will($this->returnValue($this->select));

        $this->resource = $this->getMockBuilder('\Magento\Framework\App\Resource')
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTableName'])
            ->getMock();
        $this->resource->expects($this->any())
            ->method('getConnection')
            ->with(\Magento\Framework\App\Resource::DEFAULT_READ_RESOURCE)
            ->will($this->returnValue($this->adapter));

        $this->request = $this->getMockBuilder('\Magento\Framework\Search\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getIndex'])
            ->getMockForAbstractClass();

        $this->config = $this->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isSetFlag'])
            ->getMockForAbstractClass();

        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            'Magento\CatalogSearch\Model\Search\IndexBuilder',
            [
                'resource' => $this->resource,
                'config' => $this->config,
                'storeManager' => $this->storeManager
            ]
        );
    }

    public function testBuildWithOutOfStock()
    {
        $tableSuffix = '_table';
        $index = 'test_name_of_index';

        $this->request->expects($this->once())
            ->method('getIndex')
            ->will($this->returnValue($index));

        $this->resource->expects($this->any())
            ->method('getTableName')
            ->will(
                $this->returnCallback(
                    function ($index) use ($tableSuffix) {
                        return $index . $tableSuffix;
                    }
                )
            );

        $this->select->expects($this->once())
            ->method('from')
            ->with(
                ['search_index' => $index . $tableSuffix],
                ['entity_id' => 'search_index.product_id']
            )
            ->will($this->returnSelf());

        $this->select->expects($this->at(1))
            ->method('joinLeft')
            ->with(
                ['category_index' => 'catalog_category_product_index' . $tableSuffix],
                'search_index.product_id = category_index.product_id'
                . ' AND search_index.store_id = category_index.store_id',
                []
            )
            ->will($this->returnSelf());

        $this->config->expects($this->once())
            ->method('isSetFlag')
            ->with('cataloginventory/options/show_out_of_stock')
            ->will($this->returnValue(true));

        $result = $this->target->build($this->request);
        $this->assertSame($this->select, $result);
    }

    public function testBuildWithoutOutOfStock()
    {
        $tableSuffix = '_table';
        $index = 'test_index_name';

        $this->request->expects($this->once())
            ->method('getIndex')
            ->will($this->returnValue($index));

        $this->resource->expects($this->any())
            ->method('getTableName')
            ->will(
                $this->returnCallback(
                    function ($index) use ($tableSuffix) {
                        return $index . $tableSuffix;
                    }
                )
            );

        $this->select->expects($this->once())
            ->method('from')
            ->with(
                ['search_index' => $index . $tableSuffix],
                ['entity_id' => 'search_index.product_id']
            )
            ->will($this->returnSelf());

        $this->select->expects($this->at(1))
            ->method('joinLeft')
            ->with(
                ['category_index' => 'catalog_category_product_index' . $tableSuffix],
                'search_index.product_id = category_index.product_id'
                . ' AND search_index.store_id = category_index.store_id',
                []
            )
            ->will($this->returnSelf());

        $this->config->expects($this->once())
            ->method('isSetFlag')
            ->with('cataloginventory/options/show_out_of_stock')
            ->will($this->returnValue(false));
        $this->adapter->expects($this->once())->method('quoteInto')
            ->with(' AND stock_index.website_id = ?', 1)->willReturn(' AND stock_index.website_id = 1');
        $website = $this->getMockBuilder('Magento\Store\Model\Website')->disableOriginalConstructor()->getMock();
        $website->expects($this->once())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->once())->method('getWebsite')->willReturn($website);

        $this->select->expects($this->at(2))
            ->method('joinLeft')
            ->with(
                ['stock_index' => 'cataloginventory_stock_status' . $tableSuffix],
                'search_index.product_id = stock_index.product_id'
                . ' AND stock_index.website_id = 1',
                []
            )
            ->will($this->returnSelf());
        $this->select->expects($this->once())
            ->method('where')
            ->with('stock_index.stock_status = ?', 1)
            ->will($this->returnSelf());

        $result = $this->target->build($this->request);
        $this->assertSame($this->select, $result);
    }
}
