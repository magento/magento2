<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $share = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Config\Share');

        $websiteIds = $share->getSharedWebsiteIds(42);

        $this->assertEquals(array(42), $websiteIds);
    }

    /**
     * @magentoDataFixture Magento/Core/_files/second_third_store.php
     * @magentoConfigFixture current_store customer/account_share/scope 0
     */
    public function testGetSharedWebsiteIdsMultipleSites()
    {
        /** @var Share $share */
        $share = Bootstrap::getObjectManager()->get('Magento\Customer\Model\Config\Share');
        $expectedIds = array(1);
        /** @var \Magento\Store\Model\Website $website */
        $website = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Website');
        $expectedIds[] = $website->load('secondwebsite')->getId();
        $expectedIds[] = $website->load('thirdwebsite')->getId();

        $websiteIds = $share->getSharedWebsiteIds(42);

        $this->assertEquals($expectedIds, $websiteIds);
    }
}
