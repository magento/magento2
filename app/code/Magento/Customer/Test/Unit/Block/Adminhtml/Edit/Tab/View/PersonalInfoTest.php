<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Adminhtml\Edit\Tab\View;

use Magento\Backend\Model\Session;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Log;
use Magento\Customer\Model\Logger;
use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Customer personal information template block test.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PersonalInfoTest extends TestCase
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
     * @var Log|MockObject
     */
    protected $customerLog;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $localeDate;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfig;

    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var Customer
     */
    protected $customerModel;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer->expects($this->any())->method('getId')->willReturn(1);
        $customer->expects($this->any())->method('getStoreId')->willReturn(1);

        $customerDataFactory = $this->createPartialMock(
            CustomerInterfaceFactory::class,
            ['create']
        );
        $customerDataFactory->expects($this->any())->method('create')->willReturn($customer);

        $backendSession = $this->getMockBuilder(Session::class)
            ->addMethods(['getCustomerData'])
            ->disableOriginalConstructor()
            ->getMock();
        $backendSession->expects($this->any())->method('getCustomerData')->willReturn(['account' => []]);

        $this->customerLog = $this->getMockBuilder(Log::class)
            ->addMethods(['loadByCustomer'])
            ->onlyMethods(['getLastLoginAt', 'getLastVisitAt', 'getLastLogoutAt'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerLog->expects($this->any())->method('loadByCustomer')->willReturnSelf();

        $customerLogger = $this->createPartialMock(Logger::class, ['get']);
        $customerLogger->expects($this->any())->method('get')->willReturn($this->customerLog);

        $dateTime = $this->getMockBuilder(DateTime::class)
            ->addMethods(['now'])
            ->disableOriginalConstructor()
            ->getMock();
        $dateTime->expects($this->any())->method('now')->willReturn('2015-03-04 12:00:00');

        $this->localeDate = $this->createPartialMock(
            Timezone::class,
            ['scopeDate', 'formatDateTime', 'getDefaultTimezonePath']
        );
        $this->localeDate
            ->expects($this->any())
            ->method('getDefaultTimezonePath')
            ->willReturn($this->pathToDefaultTimezone);

        $this->scopeConfig = $this->createPartialMock(Config::class, ['getValue']);
        $this->customerRegistry = $this->createPartialMock(
            CustomerRegistry::class,
            ['retrieve']
        );
        $this->customerModel = $this->createPartialMock(Customer::class, ['isCustomerLocked']);

        $objectManagerHelper = new ObjectManager($this);

        $this->block = $objectManagerHelper->getObject(
            PersonalInfo::class,
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
                ScopeInterface::SCOPE_STORE
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
                ScopeInterface::SCOPE_STORE
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
    public static function getCurrentStatusDataProvider()
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
    public static function getLastLoginDateDataProvider()
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

        $this->localeDate->expects($this->any())->method('scopeDate')->willReturn($lastLoginAt);
        $this->localeDate->expects($this->any())->method('formatDateTime')->willReturn($lastLoginAt);

        $this->assertEquals($result, $this->block->getStoreLastLoginDate());
    }

    /**
     * @return array
     */
    public static function getStoreLastLoginDateDataProvider()
    {
        return [
            ['2015-03-04 12:00:00', '2015-03-04 12:00:00'],
            ['Never', '']
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
        $expectedResult =  new Phrase($expectedResult);
        $this->assertEquals($expectedResult, $this->block->getAccountLock());
    }

    /**
     * @return array
     */
    public static function getAccountLockDataProvider()
    {
        return [
            ['expectedResult' => 'Locked', 'value' => true],
            ['expectedResult' => 'Unlocked', 'value' => false]
        ];
    }
}
