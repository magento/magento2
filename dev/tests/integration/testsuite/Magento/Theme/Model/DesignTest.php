<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model;

use Magento\Backend\Block\Widget\Grid\Serializer;
use Magento\Framework\Serialize\SerializerInterface;

class DesignTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Theme\Model\Design
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Theme\Model\Design::class
        );
    }

    public function testLoadChange()
    {
        $this->_model->loadChange(1);
        $this->assertNull($this->_model->getId());
    }

    /**
     * @magentoDataFixture Magento/Theme/_files/design_change.php
     */
    public function testChangeDesign()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\App\State::class)
            ->setAreaCode('frontend');
        $design = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\View\DesignInterface::class
        );
        $storeId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\StoreManagerInterface::class
        )->getDefaultStoreView()->getId();
        // fixture design_change
        $designChange = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Theme\Model\Design::class
        );
        $designChange->loadChange($storeId)->changeDesign($design);
        $this->assertEquals('Magento/luma', $design->getDesignTheme()->getThemePath());
    }

    public function testCRUD()
    {
        $this->_model->setData(
            [
                'store_id' => 1,
                'design' => 'Magento/blank',
                'date_from' => date('Y-m-d', strtotime('-1 day')),
                'date_to' => date('Y-m-d', strtotime('+1 day')),
            ]
        );
        $this->_model->save();
        $this->assertNotEmpty($this->_model->getId());

        try {
            $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                \Magento\Theme\Model\Design::class
            );
            $model->loadChange(1);
            $this->assertEquals($this->_model->getId(), $model->getId());

            /* Design change that intersects with existing ones should not be saved, so exception is expected */
            try {
                $model->setId(null);
                $model->save();
                $this->fail('A validation failure is expected.');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
            }

            $this->_model->delete();
        } catch (\Exception $e) {
            $this->_model->delete();
            throw $e;
        }

        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Theme\Model\Design::class
        );
        $model->loadChange(1);
        $this->assertEmpty($model->getId());
    }

    public function testCollection()
    {
        $collection = $this->_model->getCollection()->joinStore()->addDateFilter();
        /**
         * @todo fix and add addStoreFilter method
         */
        $this->assertEmpty($collection->getItems());
    }

    /**
     * @magentoDataFixture Magento/Theme/_files/design_change.php
     * @magentoConfigFixture current_store general/locale/timezone UTC
     */
    public function testLoadChangeCache()
    {
        $date = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        $storeId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\StoreManagerInterface::class
        )->getDefaultStoreView()->getId();
        // fixture design_change

        $cacheId = 'design_change_' . md5($storeId . $date);

        /** @var \Magento\Theme\Model\Design $design */
        $design = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Theme\Model\Design::class
        );
        $design->loadChange($storeId, $date);

        $cachedDesign = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\CacheInterface::class
        )->load(
            $cacheId
        );
        $serializer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(SerializerInterface::class);
        $cachedDesign = $serializer->unserialize($cachedDesign);

        $this->assertIsArray($cachedDesign);
        $this->assertArrayHasKey('design', $cachedDesign);
        $this->assertEquals($cachedDesign['design'], $design->getDesign());

        $design->setDesign('Magento/blank')->save();

        $design = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Theme\Model\Design::class
        );
        $design->loadChange($storeId, $date);

        $cachedDesign = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\CacheInterface::class
        )->load(
            $cacheId
        );

        $cachedDesign = $serializer->unserialize($cachedDesign);

        $this->assertIsArray($cachedDesign);
        $this->assertEquals($cachedDesign['design'], $design->getDesign());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Theme/_files/design_change_timezone.php
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
        } else {
            if ($storeDatetime > $utcDatetime) {
                $expectedDesign = "{$storeCode}_tomorrow_design";
            } else {
                $expectedDesign = "{$storeCode}_yesterday_design";
            }
        }

        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\StoreManagerInterface::class
        )->getStore(
            $storeCode
        );
        $defaultTimeZonePath = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class
        )->getDefaultTimezonePath();
        $store->setConfig($defaultTimeZonePath, $storeTimezone);
        $storeId = $store->getId();

        /** @var $locale \Magento\Framework\Stdlib\DateTime\TimezoneInterface */
        $locale = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $locale->expects(
            $this->once()
        )->method(
            'scopeTimeStamp'
        )->with(
            $storeId
        )->willReturn(
            $storeDatetime
        );
        // store time must stay unchanged during test execution
        $design = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Theme\Model\Design::class,
            ['localeDate' => $locale]
        );
        $design->loadChange($storeId);
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
        return [
            'default store - UTC+12:00' => ['default', 'Etc/GMT-12', '+12 hours'],
            'default store - UTC-12:00' => ['default', 'Etc/GMT+12', '-12 hours'],
            'admin store - UTC+12:00' => ['admin', 'Etc/GMT-12', '+12 hours'],
            'admin store - UTC-12:00' => ['admin', 'Etc/GMT+12', '-12 hours']
        ];
    }
}
