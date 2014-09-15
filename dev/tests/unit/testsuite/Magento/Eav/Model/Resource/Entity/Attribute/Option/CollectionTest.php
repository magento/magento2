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
namespace Magento\Eav\Model\Resource\Entity\Attribute\Option;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Option\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Core\Model\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Magento\Framework\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreResourceMock;

    /**
     * @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\Model\Resource\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Zend_Db_Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    protected function setUp()
    {
        $this->entityFactoryMock = $this->getMock('Magento\Core\Model\EntityFactory', array(), array(), '', false);
        $this->loggerMock = $this->getMock('Magento\Framework\Logger', array(), array(), '', false);
        $this->fetchStrategyMock = $this->getMock(
            'Magento\Framework\Data\Collection\Db\FetchStrategyInterface',
            array(),
            array(),
            '',
            false
        );
        $this->eventManagerMock = $this->getMock(
            'Magento\Framework\Event\ManagerInterface',
            array(),
            array(),
            '',
            false
        );
        $this->coreResourceMock = $this->getMock(
            'Magento\Framework\App\Resource',
            array('getConnection', 'getTableName'),
            array(),
            '',
            false
        );
        $this->storeManagerMock = $this->getMock(
            'Magento\Framework\StoreManagerInterface',
            array(),
            array(),
            '',
            false
        );
        $this->connectionMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', array(), array(), '', false);
        $this->resourceMock = $this->getMockForAbstractClass(
            'Magento\Framework\Model\Resource\Db\AbstractDb',
            array(),
            '',
            false,
            true,
            true,
            array('__wakeup', 'getReadConnection', 'getMainTable', 'getTable')
        );
        $this->selectMock = $this->getMock('Zend_Db_Select', array(), array(), '', false);

        $this->coreResourceMock->expects(
            $this->any()
        )->method(
            'getConnection'
        )->will(
            $this->returnValue($this->connectionMock)
        );
        $this->coreResourceMock->expects(
            $this->any()
        )->method(
            'getTableName'
        )->with(
            'eav_attribute_option_value'
        )->will(
            $this->returnValue(null)
        );

        $this->connectionMock->expects($this->any())->method('select')->will($this->returnValue($this->selectMock));
        $this->connectionMock->expects($this->any())->method('quoteIdentifier')->will($this->returnArgument(0));

        $this->resourceMock->expects(
            $this->any()
        )->method(
            'getReadConnection'
        )->will(
            $this->returnValue($this->connectionMock)
        );
        $this->resourceMock->expects(
            $this->any()
        )->method(
            'getMainTable'
        )->will(
            $this->returnValue('eav_attribute_option')
        );
        $this->resourceMock->expects(
            $this->any()
        )->method(
            'getTable'
        )->with(
            'eav_attribute_option'
        )->will(
            $this->returnValue('eav_attribute_option')
        );

        $this->model = new \Magento\Eav\Model\Resource\Entity\Attribute\Option\Collection(
            $this->entityFactoryMock,
            $this->loggerMock,
            $this->fetchStrategyMock,
            $this->eventManagerMock,
            $this->coreResourceMock,
            $this->storeManagerMock,
            null,
            $this->resourceMock
        );
    }

    public function testSetIdFilter()
    {
        $this->connectionMock->expects(
            $this->once()
        )->method(
            'prepareSqlCondition'
        )->with(
            'main_table.option_id',
            array('in' => 1)
        )->will(
            $this->returnValue('main_table.option_id IN (1)')
        );

        $this->selectMock->expects(
            $this->once()
        )->method(
            'where'
        )->with(
            'main_table.option_id IN (1)',
            null,
            'TYPE_CONDITION'
        )->will(
            $this->returnSelf()
        );

        $this->assertEquals($this->model, $this->model->setIdFilter(1));
    }
}
