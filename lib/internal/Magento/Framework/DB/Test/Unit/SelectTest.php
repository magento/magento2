<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit;

use \Magento\Framework\DB\Select;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SelectTest extends \PHPUnit_Framework_TestCase
{
    public function testWhere()
    {
        $select = new Select($this->_getConnectionMockWithMockedQuote(1, "'5'"));
        $select->from('test')->where('field = ?', 5);
        $this->assertEquals("SELECT `test`.* FROM `test` WHERE (field = '5')", $select->assemble());

        $select = new Select($this->_getConnectionMockWithMockedQuote(1, "''"));
        $select->from('test')->where('field = ?');
        $this->assertEquals("SELECT `test`.* FROM `test` WHERE (field = '')", $select->assemble());

        $select = new Select($this->_getConnectionMockWithMockedQuote(1, "'%?%'"));
        $select->from('test')->where('field LIKE ?', '%value?%');
        $this->assertEquals("SELECT `test`.* FROM `test` WHERE (field LIKE '%?%')", $select->assemble());

        $select = new Select($this->_getConnectionMockWithMockedQuote(0));
        $select->from('test')->where("field LIKE '%value?%'", null, Select::TYPE_CONDITION);
        $this->assertEquals("SELECT `test`.* FROM `test` WHERE (field LIKE '%value?%')", $select->assemble());

        $select = new Select($this->_getConnectionMockWithMockedQuote(1, "'1', '2', '4', '8'"));
        $select->from('test')->where("id IN (?)", [1, 2, 4, 8]);
        $this->assertEquals("SELECT `test`.* FROM `test` WHERE (id IN ('1', '2', '4', '8'))", $select->assemble());
    }

    /**
     * Retrieve mock of adapter with mocked quote method
     *
     * @param int $callCount
     * @param string|null $returnValue
     * @return \Magento\Framework\DB\Adapter\Pdo\Mysql|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getConnectionMockWithMockedQuote($callCount, $returnValue = null)
    {
        $connection = $this->getMock(
            '\Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['supportStraightJoin', 'quote'],
            [],
            '',
            false
        );
        $method = $connection->expects($this->exactly($callCount))->method('quote');
        if ($callCount > 0) {
            $method->will($this->returnValue($returnValue));
        }
        return $connection;
    }
}
