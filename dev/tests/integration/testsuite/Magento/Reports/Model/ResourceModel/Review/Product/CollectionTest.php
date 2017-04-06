<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\ResourceModel\Review\Product;

/**
 * @magentoAppArea adminhtml
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Review\Product\Collection
     */
    private $_collection;

    protected function setUp()
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Reports\Model\ResourceModel\Review\Product\Collection::class
        );
    }

    public function testGetSelect()
    {
        $select = (string)$this->_collection->getSelect();
        $search = '/SUM\(table_rating.percent\)\/COUNT\(table_rating.rating_id\) AS `avg_rating`'
            . '[\s\S]+SUM\(table_rating.percent_approved\)\/COUNT\(table_rating.rating_id\) AS `avg_rating_approved`'
            . '[\s\S]+LEFT JOIN `.*rating_option_vote_aggregated` AS `table_rating`/';

        $this->assertRegExp($search, $select);
    }
}
