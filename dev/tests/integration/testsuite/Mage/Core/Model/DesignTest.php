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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_DesignTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Design
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new Mage_Core_Model_Design();
    }

    public function testLoadChange()
    {
        $this->_model->loadChange(1);
        $this->assertNull($this->_model->getId());
    }

    /**
     * @magentoDataFixture Mage/Core/_files/design_change.php
     */
    public function testChangeDesign()
    {
        $designPackage = new Mage_Core_Model_Design_Package('frontend', 'default', 'default', 'default');
        $storeId = Mage::app()->getAnyStoreView()->getId(); // fixture design_change
        $design = new Mage_Core_Model_Design;
        $design->loadChange($storeId)->changeDesign($designPackage);
        $this->assertEquals('default/modern/default', $designPackage->getDesignTheme());
    }

    public function testCRUD()
    {
        $this->_model->setData(
            array(
                'store_id'  => 1,
                'design'    => 'default/default/default',
                /* Note: in order to load a design change it should be active within the store's time zone */
                'date_from' => date('Y-m-d', strtotime('-1 day')),
                'date_to'   => date('Y-m-d', strtotime('+1 day')),
            )
        );
        $this->_model->save();
        $this->assertNotEmpty($this->_model->getId());

        try {
            $model =  new Mage_Core_Model_Design();
            $model->loadChange(1);
            $this->assertEquals($this->_model->getId(), $model->getId());

            /* Design change that intersects with existing ones should not be saved, so exception is expected */
            try {
                $model->setId(null);
                $model->save();
                $this->fail('A validation failure is expected.');
            } catch (Mage_Core_Exception $e) {
                // intentionally swallow exception
            }

            $this->_model->delete();
        } catch (Exception $e) {
            $this->_model->delete();
            throw $e;
        }

        $model =  new Mage_Core_Model_Design();
        $model->loadChange(1);
        $this->assertEmpty($model->getId());
    }

    public function testCollection()
    {
        $collection = $this->_model->getCollection()
            ->joinStore()
            ->addDateFilter();
        /**
         * @todo fix and add addStoreFilter method
         */
        $this->assertEmpty($collection->getItems());
    }

    /**
     * @magentoDataFixture Mage/Core/_files/design_change.php
     * @magentoConfigFixture current_store general/locale/timezone UTC
     */
    public function testLoadChangeCache()
    {
        $date = Varien_Date::now(true);
        $storeId = Mage::app()->getAnyStoreView()->getId(); // fixture design_change

        $cacheId = 'design_change_' . md5($storeId . $date);

        $design = new Mage_Core_Model_Design;
        $design->loadChange($storeId, $date);

        $cachedDesign = Mage::app()->loadCache($cacheId);
        $cachedDesign = unserialize($cachedDesign);

        $this->assertInternalType('array', $cachedDesign);
        $this->assertArrayHasKey('design', $cachedDesign);
        $this->assertEquals($cachedDesign['design'], $design->getDesign());

        $design->setDesign('default/default/default')->save();

        $design = new Mage_Core_Model_Design;
        $design->loadChange($storeId, $date);

        $cachedDesign = Mage::app()->loadCache($cacheId);
        $cachedDesign = unserialize($cachedDesign);

        $this->assertTrue(is_array($cachedDesign));
        $this->assertEquals($cachedDesign['design'], $design->getDesign());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Core/_files/design_change_timezone.php
     * @dataProvider loadChangeTimezoneDataProvider
     */
    public function testLoadChangeTimezone($storeCode, $storeTimezone, $storeUtcOffset)
    {
        if (date_default_timezone_get() != 'UTC') {
            $this->markTestSkipped('Test requires UTC to be the default timezone.');
        }
        $utcDatetime = time();
        $utcDate = date('Y-m-d', $utcDatetime);
        $storeDatetime = strtotime($storeUtcOffset, $utcDatetime);
        $storeDate = date('Y-m-d', $storeDatetime);

        if ($storeDate == $utcDate) {
            $expectedDesign = "{$storeCode}_today_design";
        } else if ($storeDatetime > $utcDatetime) {
            $expectedDesign = "{$storeCode}_tomorrow_design";
        } else {
            $expectedDesign = "{$storeCode}_yesterday_design";
        }

        $store = Mage::app()->getStore($storeCode);
        $store->setConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE, $storeTimezone);

        $design = new Mage_Core_Model_Design;
        $design->loadChange($store->getId());
        $actualDesign = $design->getDesign();

        $this->assertEquals($expectedDesign, $actualDesign);
    }

    public function loadChangeTimezoneDataProvider()
    {
        /**
         * Depending on the current UTC time, either UTC-12:00, or UTC+12:00 timezone points to the different date.
         * If UTC time is between 00:00 and 12:00, UTC+12:00 points to the same day, and UTC-12:00 to the previous day.
         * If UTC time is between 12:00 and 24:00, UTC-12:00 points to the same day, and UTC+12:00 to the next day.
         * Testing the design change with both UTC-12:00 and UTC+12:00 store timezones guarantees
         * that the proper design change is chosen for the timezone with the date different from the UTC.
         */
        return array(
            'default store - UTC+12:00' => array(
                'default',
                'Etc/GMT-12',  // "GMT-12", not "GMT+12", see http://www.php.net/manual/en/timezones.others.php#64310
                '+12 hours',
            ),
            'default store - UTC-12:00' => array(
                'default',
                'Etc/GMT+12',
                '-12 hours',
            ),
            'admin store - UTC+12:00' => array(
                'admin',
                'Etc/GMT-12',
                '+12 hours',
            ),
            'admin store - UTC-12:00' => array(
                'admin',
                'Etc/GMT+12',
                '-12 hours',
            ),
        );
    }
}
