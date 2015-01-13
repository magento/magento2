<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Collection\Db\FetchStrategy;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    public function testFetchAll()
    {
        $expectedResult = new \stdClass();
        $bindParams = ['param_one' => 'value_one', 'param_two' => 'value_two'];
        $adapter = $this->getMockForAbstractClass(
            'Zend_Db_Adapter_Abstract',
            [],
            '',
            false,
            true,
            true,
            ['fetchAll']
        );
        $select = new \Zend_Db_Select($adapter);
        $adapter->expects(
            $this->once()
        )->method(
            'fetchAll'
        )->with(
            $select,
            $bindParams
        )->will(
            $this->returnValue($expectedResult)
        );
        $object = new \Magento\Framework\Data\Collection\Db\FetchStrategy\Query();
        $this->assertSame($expectedResult, $object->fetchAll($select, $bindParams));
    }
}
