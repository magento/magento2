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
namespace Magento\Sales\Model\Resource\Order\Invoice;

/**
 * Class GridTest
 */
class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\Order\Invoice\Grid|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $grid;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $appResourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \Zend_Db_Statement_Interface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $statementMock;

    public function setUp()
    {
        $this->appResourceMock = $this->getMock(
            'Magento\Framework\App\Resource',
            [],
            [],
            '',
            false
        );
        $this->eventManagerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Event\ManagerInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->modelMock = $this->getMockForAbstractClass(
            'Magento\Sales\Model\AbstractModel',
            [],
            '',
            false,
            false,
            true,
            ['__wakeup', 'getId']
        );
        $this->adapterMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['select', 'query', 'insertFromSelect', 'delete'],
            [],
            '',
            false
        );
        $this->selectMock = $this->getMock(
            'Magento\Framework\DB\Select',
            [],
            [],
            '',
            false
        );
        $this->statementMock = $this->getMockForAbstractClass(
            'Zend_Db_Statement_Interface',
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->grid = new \Magento\Sales\Model\Resource\Order\Invoice\Grid(
            $this->appResourceMock
        );
    }

    /**
     * Test refresh method
     */
    public function testRefresh()
    {
        $this->appResourceMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($this->adapterMock));
        $this->appResourceMock->expects($this->any())
            ->method('getTableName')
            ->will($this->returnValue('sales_flat_invoice_grid'));
        $this->adapterMock->expects($this->once())
            ->method('select')
            ->will($this->returnValue($this->selectMock));
        $this->selectMock->expects($this->once())
            ->method('from')
            ->will($this->returnSelf());
        $this->selectMock->expects($this->once())
            ->method('join')
            ->will($this->returnSelf());
        $this->selectMock->expects($this->once())
            ->method('joinLeft')
            ->will($this->returnSelf());
        $this->selectMock->expects($this->once())
            ->method('columns')
            ->will($this->returnSelf());
        $this->selectMock->expects($this->once())
            ->method('where')
            ->with('fi.field = ?', 1, null)
            ->will($this->returnSelf());
        $this->adapterMock->expects($this->once())
            ->method('query')
            ->with('sql-query')
            ->will($this->returnValue($this->statementMock));
        $this->adapterMock->expects($this->once())
            ->method('insertFromSelect')
            ->with($this->selectMock, 'sales_flat_invoice_grid', [], 1)
            ->will($this->returnValue('sql-query'));
        $this->assertEquals($this->statementMock, $this->grid->refresh(1, 'fi.field'));
    }

    /**
     * Test purge method
     */
    public function testPurge()
    {
        $this->appResourceMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($this->adapterMock));
        $this->appResourceMock->expects($this->once())
            ->method('getTableName')
            ->will($this->returnValue('sales_flat_invoice_grid'));
        $this->adapterMock->expects($this->once())
            ->method('delete')
            ->with('sales_flat_invoice_grid', ['fi.field = ?' => 1])
            ->will($this->returnValue(1));
        $this->assertEquals(1, $this->grid->purge(1, 'fi.field'));
    }
}
