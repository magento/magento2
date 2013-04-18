<?php
/**
 * Core module API tests.
 *
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Magento store Api tests
 *
 * @magentoDataFixture Mage/Core/_files/store.php
 */
class Mage_Core_Model_Store_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test store info.
     */
    public function testInfo()
    {
        $expectedStore = Mage::app()->getStore('fixturestore');
        $storeInfo = Magento_Test_Helper_Api::call($this, 'storeInfo', array(
            'storeId' => 'fixturestore',
        ));
        $expectedData= $expectedStore->getData();
        $this->assertEquals($expectedData, $storeInfo);
    }

    /**
     * Test stores list.
     */
    public function testList()
    {
        $actualStores = Magento_Test_Helper_Api::call($this, 'storeList');
        $expectedStores = Mage::app()->getStores();
        /** @var Mage_Core_Model_Store $expectedStore */
        foreach ($expectedStores as $expectedStore) {
            $expectedStoreFound = false;
            foreach ($actualStores as $actualStore) {
                if ($actualStore['store_id'] == $expectedStore->getId()) {
                    $this->assertEquals($expectedStore->getData(), $actualStore);
                    $expectedStoreFound = true;
                }
            }
            if (!$expectedStoreFound) {
                $this->fail(sprintf('Store "%s" was not found in API response.', $expectedStore->getFrontendName()));
            }
        }
    }
}
