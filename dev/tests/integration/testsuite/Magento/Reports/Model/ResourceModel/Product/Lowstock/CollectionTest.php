<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model\ResourceModel\Product\Lowstock;

/**
 * Class CollectionTest
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Reports\Model\ResourceModel\Product\Lowstock\Collection
     */
    private $collection;

    protected function setUp()
    {
        /**
         * @var  \Magento\Reports\Model\ResourceModel\Product\Lowstock\Collection
         */
        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Reports\Model\ResourceModel\Product\Lowstock\Collection::class
        );
    }

    /**
     * Assert that filterByProductType method throws LocalizedException if not String or Array is passed to it
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testFilterByProductTypeException()
    {
        $this->collection->filterByProductType(100);
    }

    /**
     * Assert that String argument passed to filterByProductType method is correctly passed to attribute adder
     *
     */
    public function testFilterByProductTypeString()
    {
        $this->collection->filterByProductType('simple');
        $whereParts = $this->collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
        $this->assertContains('simple', $whereParts[0]);
    }

    /**
     * Assert that Array argument passed to filterByProductType method is correctly passed to attribute adder
     *
     */
    public function testFilterByProductTypeArray()
    {
        $this->collection->filterByProductType(['simple', 'configurable']);
        $whereParts = $this->collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);

        $this->assertThat(
            $whereParts[0],
            $this->logicalAnd(
                $this->stringContains('simple'),
                $this->stringContains('configurable')
            )
        );
    }
}
