<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model;

class GetDefaultStoreGroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testExecute()
    {
        $getDefaultStoreGroupModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\GetDefaultStoreGroup::class
        );

        $group = $getDefaultStoreGroupModel->execute();

        $this->assertInstanceOf(\Magento\Store\Api\Data\GroupInterface::class, $group);
        $this->assertNotNull($group->getId());
        $this->assertEquals('main_website_store', $group->getCode());
    }
}
