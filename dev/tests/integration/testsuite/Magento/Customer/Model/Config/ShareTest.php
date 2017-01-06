<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Config;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test \Magento\Customer\Model\Config\Share
 */
class ShareTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSharedWebsiteIds()
    {
        /** @var Share $share */
        $share = Bootstrap::getObjectManager()->get(\Magento\Customer\Model\Config\Share::class);

        $websiteIds = $share->getSharedWebsiteIds(42);

        $this->assertEquals([42], $websiteIds);
    }

    /**
     * @magentoDataFixture Magento/Store/_files/core_second_third_fixturestore.php
     * @magentoConfigFixture current_store customer/account_share/scope 0
     */
    public function testGetSharedWebsiteIdsMultipleSites()
    {
        /** @var Share $share */
        $share = Bootstrap::getObjectManager()->get(\Magento\Customer\Model\Config\Share::class);
        $expectedIds = [1];
        /** @var \Magento\Store\Model\Website $website */
        $website = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Store\Model\Website::class
        );
        $expectedIds[] = $website->load('secondwebsite')->getId();
        $expectedIds[] = $website->load('thirdwebsite')->getId();

        $websiteIds = $share->getSharedWebsiteIds(42);

        $this->assertEquals($expectedIds, $websiteIds);
    }
}
