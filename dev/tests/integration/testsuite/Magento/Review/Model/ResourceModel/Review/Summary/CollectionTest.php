<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Model\ResourceModel\Summary;

/**
 * Tests some functionality of the Product Review collection
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Review\Model\ResourceModel\Review\Summary\Collection::class
        );
    }
    
    /**
     * @param mixed $storeId
     * @dataProvider storeIdDataProvider
     */
    public function testAddStoreFilter($storeId) {
        $expectedWhere = is_numeric($storeId) ? 'store_id = ?' : 'store_id IN (?)';

        $select = $this->createPartialMock(\Magento\Framework\DB\Select::class, ['where']);
        $select->expects(
            $this->any()
        )->method(
            'where'
        )->with(
            $this->equalTo($expectedWhere)
        )->willReturnSelf(

        );

        $this->assertEquals($this->_model, $this->_model->addStoreFilter($storeId));
    }

    /**
     * @return array
     */
    public function storeIdDataProvider(): array
    {
        return [
            [1],
           [1, [1,2]]
        ];
    }
}
