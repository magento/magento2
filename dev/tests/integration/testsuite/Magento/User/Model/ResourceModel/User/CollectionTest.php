<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Model\ResourceModel\User;

/**
 * User collection test
 * @magentoAppArea adminhtml
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\User\Model\ResourceModel\User\Collection
     */
    protected $_collection;

    protected function setUp(): void
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\User\Model\ResourceModel\User\Collection::class
        );
    }

    public function testFilteringCollectionByUserId()
    {
        $this->assertEquals(1, $this->_collection->addFieldToFilter('user_id', 1)->count());
    }
}
