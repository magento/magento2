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
            ->setMethods(['select'])
            ->getMockForAbstractClass();
        $this->adapter->expects($this->once())
            ->method('select')
            ->will($this->returnValue($this->select));

        $this->resource = $this->getMockBuilder('\Magento\Framework\App\Resource')
            ->disableOriginalConstructor()
            ->setMethods(['getConnection', 'getTableName'])
            ->getMock();
        $this->resource->expects($this->once())
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

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            'Magento\CatalogSearch\Model\Search\IndexBuilder',
            [
                'resource' => $this->resource,
                'config' => $this->config,
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

        $this->select->expects($this->at(2))
            ->method('joinLeft')
            ->with(
                ['stock_index' => 'cataloginventory_stock_status' . $tableSuffix],
                'search_index.product_id = stock_index.product_id'
                . ' AND stock_index.website_id = 1 AND stock_index.stock_id = 1',
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
