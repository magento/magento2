<?php
/**
 * \Magento\Customer\Model\ResourceModel\Customer\Collection
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Customer;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected $_collection;

    public function setUp()
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\ResourceModel\Customer\Collection'
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
