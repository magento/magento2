<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Model\ResourceModel\Role\User;

/**
 * Role user collection test
 * @magentoAppArea adminhtml
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\User\Model\ResourceModel\Role\User\Collection
     */
    protected $_collection;

    protected function setUp()
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\User\Model\ResourceModel\Role\User\Collection'
        );
    }

    public function testSelectQueryInitialized()
    {
        $this->assertContains('user_id > 0', $this->_collection->getSelect()->__toString());
    }
}
