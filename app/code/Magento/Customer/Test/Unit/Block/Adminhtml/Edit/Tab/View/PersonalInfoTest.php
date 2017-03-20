<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo;
use Magento\Framework\Stdlib\DateTime;

/**
 * Customer personal information template block test.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PersonalInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $defaultTimezone = 'America/Los_Angeles';

    /**
     * @var string
     */
    protected $pathToDefaultTimezone = 'path/to/default/timezone';

    /**
     * @var PersonalInfo
     */
    protected $block;

    /**
     * @var \Magento\Customer\Model\Log|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerLog;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerModel;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $customer = $this->getMock(
            \Magento\Customer\Api\Data\CustomerInterface::class,
            [],
            [],
            '',
            false
        );
        $customer->expects($this->any())->method('getId')->willReturn(1);
        $customer->expects($this->any())->method('getStoreId')->willReturn(1);

        $customerDataFactory = $this->getMock(
            \Magento\Customer\Api\Data\CustomerInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $customerDataFactory->expects($this->any())->method('create')->willReturn($customer);

        $backendSession = $this->getMock(
            \Magento\Backend\Model\Session::class,
            ['getCustomerData'],
            [],
            '',
            false
        );
        $backendSession->expects($this->any())->method('getCustomerData')->willReturn(['account' => []]);

        $this->customerLog = $this->getMock(
            \Magento\Customer\Model\Log::class,
            ['getLastLoginAt', 'getLastVisitAt', 'getLastLogoutAt'],
            [],
            '',
            false
        );
        $this->customerLog->expects($this->any())->method('loadByCustomer')->willReturnSelf();

        $customerLogger = $this->getMock(
            \Magento\Customer\Model\Logger::class,
            ['get'],
            [],
            '',
            false
        );
        $customerLogger->expects($this->any())->method('get')->willReturn($this->customerLog);

        $dateTime = $this->getMock(
            \Magento\Framework\Stdlib\DateTime::class,
            ['now'],
            [],
            '',
            false
        );
        $dateTime->expects($this->any())->method('now')->willReturn('2015-03-04 12:00:00');

        $this->localeDate = $this->getMock(
            \Magento\Framework\Stdlib\DateTime\Timezone::class,
            ['scopeDate', 'formatDateTime', 'getDefaultTimezonePath'],
            [],
            '',
            false
        );
        $this->localeDate
            ->expects($this->any())
            ->method('getDefaultTimezonePath')
            ->willReturn($this->pathToDefaultTimezone);

        $this->scopeConfig = $this->getMock(
            \Magento\Framework\App\Config::class,
            ['getValue'],
            [],
            '',
            false
        );
        $this->customerRegistry = $this->getMock(
            \Magento\Customer\Model\CustomerRegistry::class,
            ['retrieve'],
            [],
            '',
            false
        );
        $this->customerModel = $this->getMock(
            \Magento\Customer\Model\Customer::class,
            ['isCustomerLocked'],
            [],
            '',
            false
        );

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->block = $objectManagerHelper->getObject(
            \Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo::class,
            [
                'customerDataFactory' => $customerDataFactory,
                'dateTime' => $dateTime,
                'customerLogger' => $customerLogger,
                'localeDate' => $this->localeDate,
                'scopeConfig' => $this->scopeConfig,
                'backendSession' => $backendSession,
            ]
        );
        $this->block->setCustomerRegistry($this->customerRegistry);
    }

    /**
     * @return void
     */
    public function testGetStoreLastLoginDateTimezone()
    {
        $this->scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->with(
                $this->pathToDefaultTimezone,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn($this->defaultTimezone);

        $this->assertEquals($this->defaultTimezone, $this->block->getStoreLastLoginDateTimezone());
    }

    /**
     * @param string $status
     * @param string|null $lastLoginAt
     * @param string|null $lastVisitAt
     * @param string|null $lastLogoutAt
     * @return void
     * @dataProvider getCurrentStatusDataProvider
     */
    public function testGetCurrentStatus($status, $lastLoginAt, $lastVisitAt, $lastLogoutAt)
    {
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with(
                'customer/online_customers/online_minutes_interval',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ->willReturn(240); //TODO: it's value mocked because unit tests run data providers before all testsuite

        $this->customerLog->expects($this->any())->method('getLastLoginAt')->willReturn($lastLoginAt);
        $this->customerLog->expects($this->any())->method('getLastVisitAt')->willReturn($lastVisitAt);
        $this->customerLog->expects($this->any())->method('getLastLogoutAt')->willReturn($lastLogoutAt);

        $this->assertEquals($status, (string) $this->block->getCurrentStatus());
    }

    /**
     * @return array
     */
    public function getCurrentStatusDataProvider()
    {
        return [
            ['Offline', null, null, null],
            ['Offline', '2015-03-04 11:00:00', null, '2015-03-04 12:00:00'],
            ['Offline', '2015-03-04 11:00:00', '2015-03-04 11:40:00', null],
            ['Online', '2015-03-04 11:00:00', (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT), null]
        ];
    }

    /**
     * @param string $result
     * @param string|null $lastLoginAt
     * @dataProvider getLastLoginDateDataProvider
     * @return void
     */
    public function testGetLastLoginDate($result, $lastLoginAt)
    {
        $this->customerLog->expects($this->once())->method('getLastLoginAt')->willReturn($lastLoginAt);
        $this->localeDate->expects($this->any())->method('formatDateTime')->willReturn($lastLoginAt);

        $this->assertEquals($result, $this->block->getLastLoginDate());
    }

    /**
     * @return array
     */
    public function getLastLoginDateDataProvider()
    {
        return [
            ['2015-03-04 12:00:00', '2015-03-04 12:00:00'],
            ['Never', null]
        ];
    }

    /**
     * @param string $result
     * @param string|null $lastLoginAt
     * @dataProvider getStoreLastLoginDateDataProvider
     * @return void
     */
    public function testGetStoreLastLoginDate($result, $lastLoginAt)
    {
        $this->customerLog->expects($this->once())->method('getLastLoginAt')->willReturn($lastLoginAt);

        $this->localeDate->expects($this->any())->method('scopeDate')->will($this->returnValue($lastLoginAt));
        $this->localeDate->expects($this->any())->method('formatDateTime')->willReturn($lastLoginAt);

        $this->assertEquals($result, $this->block->getStoreLastLoginDate());
    }

    /**
     * @return array
     */
    public function getStoreLastLoginDateDataProvider()
    {
        return [
            ['2015-03-04 12:00:00', '2015-03-04 12:00:00'],
            ['Never', null]
        ];
    }

    /**
     * @param string $expectedResult
     * @param bool $value
     * @dataProvider getAccountLockDataProvider
     * @return void
     */
    public function testGetAccountLock($expectedResult, $value)
    {
        $this->customerRegistry->expects($this->once())->method('retrieve')->willReturn($this->customerModel);
        $this->customerModel->expects($this->once())->method('isCustomerLocked')->willReturn($value);
        $expectedResult =  new \Magento\Framework\Phrase($expectedResult);
        $this->assertEquals($expectedResult, $this->block->getAccountLock());
    }

    /**
     * @return array
     */
    public function getAccountLockDataProvider()
    {
        return [
            ['result' => 'Locked', 'expectedValue' => true],
            ['result' => 'Unlocked', 'expectedValue' => false]
        ];
    }
}
