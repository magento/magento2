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

namespace Magento\Catalog\Model\Resource\Product\Indexer\Eav;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class SourceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Model\Resource\Product\Indexer\Eav\Source */
    protected $source;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;

    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $managerInterface;

    /** @var \Magento\Catalog\Model\Resource\Helper|\PHPUnit_Framework_MockObject_MockObject */
    protected $helper;

    protected function setUp()
    {
        $this->resource = $this->getMock(
            'Magento\Framework\App\Resource',
            ['getConnection', 'getTableName'],
            [],
            '',
            false
        );
        $this->config = $this->getMock('Magento\Eav\Model\Config', [], [], '', false);
        $this->managerInterface = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $this->helper = $this->getMock('Magento\Catalog\Model\Resource\Helper', [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->source = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Model\Resource\Product\Indexer\Eav\Source',
            [
                'resource' => $this->resource,
                'eavConfig' => $this->config,
                'eventManager' => $this->managerInterface,
                'resourceHelper' => $this->helper
            ]
        );
    }

    /**
     * Test `reindexEntity` method
     */
    public function testReindexEntities()
    {
        $query = $this->getMockBuilder('PDO_Statement')->setMethods(['fetch'])->disableOriginalConstructor()->getMock();
        $query->expects($this->any())->method('fetch')->will($this->returnValue([]));

        $select = $this->getMockBuilder('\Magento\Framework\DB\Select')->setMethods([
                'select', 'from', 'where', 'join', 'joinLeft', 'joinInner',
                'assemble', 'columns', 'insertFromSelect', 'query', 'deleteFromSelect'
            ])->disableOriginalConstructor()->getMock();
        $select->expects($this->any())->method('from')->withAnyParameters()->will($this->returnSelf());
        $select->expects($this->any())->method('where')->will($this->returnSelf());
        $select->expects($this->any())->method('join')->will($this->returnSelf());
        $select->expects($this->any())->method('query')->will($this->returnValue($query));
        $select->expects($this->any())->method('columns')->will($this->returnSelf());
        $select->expects($this->any())->method('joinLeft')->will($this->returnSelf());
        $select->expects($this->any())->method('insertFromSelect')->will($this->returnSelf());
        $select->expects($this->any())->method('deleteFromSelect')->with('catalog_product_index_eav_tmp')
            ->will($this->returnValue($query));
        $select->expects($this->once())->method('joinInner')
            ->with(
                array('d2' => 'catalog_product_entity_int'),
                'd.entity_id = d2.entity_id AND d2.attribute_id = 96 AND d2.value = 1 AND d.store_id = 0'
            )->will($this->returnSelf());

        $adapter = $this->getMockBuilder('Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->setMethods([
                'select', 'delete', 'beginTransaction', 'getTransactionLevel', 'fetchCol', 'query', 'quoteInto',
                'describeTable', 'commit'
            ])->disableOriginalConstructor()->getMock();
        $adapter->expects($this->any())->method('select')->will($this->returnValue($select));
        $adapter->expects($this->any())->method('getTransactionLevel')->will($this->returnValue(1));
        $adapter->expects($this->any())->method('fetchCol')->will($this->returnValue([1]));
        $adapter->expects($this->any())->method('query')->will($this->returnValue($query));
        $adapter->expects($this->any())->method('describeTable')->will($this->returnValue([]));
        $adapter->expects($this->any())->method('commit')->will($this->returnValue(null));


        $this->resource->expects($this->any())->method('getConnection')->with('core_write')
            ->will($this->returnValue($adapter));
        $this->resource->expects($this->at(4))->method('getTableName')->with('catalog_product_index_eav_tmp')
            ->will($this->returnArgument(0));
        $this->resource->expects($this->at(8))->method('getTableName')->with('catalog_product_entity_int')
            ->will($this->returnArgument(0));


        $attribute = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute')->disableOriginalConstructor()
            ->setMethods(['getId', '__sleep', '__wakeup', 'getBackend', 'getTable', 'isScopeGlobal'])->getMock();
        $attribute->expects($this->once())->method('getId')->will($this->returnValue(96));
        $attribute->expects($this->any())->method('getBackend')->will($this->returnSelf());
        $attribute->expects($this->any())->method('getTable')->will($this->returnValue('some_table'));
        $attribute->expects($this->any())->method('isScopeGlobal')->will($this->returnValue(true));
        $this->config->expects($this->any())->method('getAttribute')->will($this->returnValue($attribute));

        $this->source->reindexEntities([1]);
    }
}
