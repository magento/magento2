<?php
/**
 * \Magento\Customer\Model\ResourceModel\Customer\Collection
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Customer\Model\ResourceModel\Customer;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected $_collection;

    protected function setUp(): void
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\ResourceModel\Customer\Collection::class
        );
    }

    public function testAddNameToSelect()
    {
        $this->_collection->addNameToSelect();
        $joinParts = $this->_collection->getSelect()->getPart(\Magento\Framework\DB\Select::FROM);

        $this->assertArrayHasKey('e', $joinParts);
        $this->assertCount(1, $joinParts);
    }
}
