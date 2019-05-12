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
    protected $collection;

    protected function setUp()
    {
        $this->collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\User\Model\ResourceModel\User\Collection::class
        );
    }

    public function testSelectQueryInitialized()
    {
        static::assertContains(
            'main_table.user_id = user_role.user_id AND user_role.parent_id != 0',
            $this->collection->getSelect()->__toString()
        );
    }

    /**
     * @magentoDataFixture Magento/User/_files/expired_users.php
     */
    public function testExpiresAtFilter()
    {
        $this->collection->addExpiresAtFilter();
        static::assertCount(1, $this->collection);
    }
}
