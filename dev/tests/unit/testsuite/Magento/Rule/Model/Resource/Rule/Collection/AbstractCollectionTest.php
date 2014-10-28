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

namespace Magento\Rule\Model\Resource\Rule\Collection;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class AbstractCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Rule\Model\Resource\Rule\Collection\AbstractCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $abstractCollection;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_entityFactoryMock;

    /**
     * @var \Magento\Framework\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_loggerMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fetchStrategyMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_managerMock;

    /**
     * @var \Magento\Framework\Model\Resource\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_db;

    protected function setUp()
    {
        $this->_entityFactoryMock = $this->getMock('Magento\Framework\Data\Collection\EntityFactoryInterface');
        $this->_loggerMock = $this->getMock('Magento\Framework\Logger', [], [], '', false);
        $this->_fetchStrategyMock = $this->getMock('Magento\Framework\Data\Collection\Db\FetchStrategyInterface');
        $this->_managerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $this->_db = $this->getMockForAbstractClass(
            '\Magento\Framework\Model\Resource\Db\AbstractDb',
            [],
            '',
            false,
            false,
            true,
            ['__sleep', '__wakeup']
        );
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->abstractCollection = $this->getMockForAbstractClass(
            '\Magento\Rule\Model\Resource\Rule\Collection\AbstractCollection',
            [
                'entityFactory' => $this->_entityFactoryMock,
                'logger' => $this->_loggerMock,
                'fetchStrategy' => $this->_fetchStrategyMock,
                'eventManager' => $this->_managerMock,
                null,
                $this->_db
            ],
            '',
            false,
            false,
            true,
            ['__sleep', '__wakeup', '_getAssociatedEntityInfo', 'getConnection', 'getSelect', 'getTable']
        );
    }

    public function testAddWebsitesToResultDataProvider()
    {
        return [
            [null, true],
            [true, true],
            [false, false]
        ];
    }

    /**
     * @dataProvider testAddWebsitesToResultDataProvider
     */
    public function testAddWebsitesToResult($flag, $expectedResult)
    {
        $this->abstractCollection->addWebsitesToResult($flag);
        $this->assertEquals($expectedResult, $this->abstractCollection->getFlag('add_websites_to_result'));
    }

    protected function _prepareAddFilterStubs()
    {
        $entityInfo = [];
        $entityInfo['entity_id_field'] = 'entity_id';
        $entityInfo['rule_id_field'] = 'rule_id';
        $entityInfo['associations_table'] = 'assoc_table';

        $connection = $this->getMock('\Magento\Framework\DB\Adapter\AdapterInterface');
        $select = $this->getMock('\Magento\Framework\DB\Select', [], [], '', false);
        $collectionSelect = $this->getMock('\Magento\Framework\DB\Select', [], [], '', false);

        $connection->expects($this->any())
            ->method('select')
            ->will($this->returnValue($select));

        $select->expects($this->any())
            ->method('from')
            ->will($this->returnSelf());

        $select->expects($this->any())
            ->method('where')
            ->will($this->returnSelf());

        $collectionSelect->expects($this->once())
            ->method('exists');

        $this->abstractCollection->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connection));

        $this->_db->expects($this->any())
            ->method('getTable')
            ->will($this->returnArgument(0));

        $this->abstractCollection->expects($this->any())
            ->method('getSelect')
            ->will($this->returnValue($collectionSelect));

        $this->abstractCollection->expects($this->any())
            ->method('_getAssociatedEntityInfo')
            ->will($this->returnValue($entityInfo));
    }

    public function testAddWebsiteFilter()
    {
        $this->_prepareAddFilterStubs();
        $website = $this->getMock('\Magento\Store\Model\Website', ['getId', '__sleep', '__wakeup'], [], '', false);

        $website->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $this->assertInstanceOf(
            '\Magento\Rule\Model\Resource\Rule\Collection\AbstractCollection',
            $this->abstractCollection->addWebsiteFilter($website)
        );
    }

    public function testAddFieldToFilter()
    {
        $this->_prepareAddFilterStubs();
        $this->abstractCollection->addFieldToFilter('website_ids', []);
    }
}
