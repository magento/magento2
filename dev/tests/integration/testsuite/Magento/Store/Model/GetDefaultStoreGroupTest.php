<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:disable Magento2.Security.Superglobal
 */
class GetDefaultStoreGroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\GetDefaultStoreGroup
     */
    protected $getDefaultStoreGroupModel;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->getDefaultStoreGroupModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\GetDefaultStoreGroup::class
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->getDefaultStoreGroupModel = null;
    }

    /**
     * @magentoDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testExecute()
    {
        $group = $this->getDefaultStoreGroupModel->execute();

        $this->assertInstanceOf(\Magento\Store\Api\Data\GroupInterface::class, $group);
        $this->assertNotNull($group->getId());
        $this->assertEquals("main_website_store", $group->getCode());
    }
}
