<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\View;

/**
 * Customer personal information template block test.
 *
 * @package Magento\Customer\Block\Adminhtml\Edit\Tab\View
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
     * @var \Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo
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

    protected function setUp()
    {
        $customer = $this->getMock(
            'Magento\Customer\Api\Data\CustomerInterface', [], [], '', false
        );

        $customer->expects($this->any())->method('getId')->willReturn(1);
        $customer->expects($this->any())->method('getStoreId')->willReturn(1);

        $customerDataFactory = $this->getMock(
            'Magento\Customer\Api\Data\CustomerInterfaceFactory', ['create'], [], '', false
        );

        $customerDataFactory->expects($this->any())->method('create')->willReturn($customer);

        $backendSession = $this->getMock(
            'Magento\Backend\Model\Session', ['getCustomerData'], [], '', false
        );

        $backendSession->expects($this->any())->method('getCustomerData')->willReturn(['account' => []]);

        $this->customerLog = $this->getMock(
            'Magento\Customer\Model\Log',
            ['loadByCustomer', 'getLastLoginAt', 'getLastVisitAt', 'getLastLogoutAt'],
            [],
            '',
            false
        );

        $this->customerLog->expects($this->any())->method('loadByCustomer')->willReturnSelf();

        $dateTime = $this->getMock(
            'Magento\Framework\Stdlib\DateTime', ['now'], [], '', false
        );

        $dateTime->expects($this->any())->method('now')->willReturn('2015-03-04 12:00:00');

        $this->localeDate = $this->getMock(
            'Magento\Framework\Stdlib\DateTime\Timezone',
            ['scopeDate', 'formatDate', 'getDefaultTimezonePath'],
            [],
            '',
            false
        );

        $this->localeDate
            ->expects($this->any())
            ->method('getDefaultTimezonePath')
            ->willReturn($this->pathToDefaultTimezone);

        $this->scopeConfig = $this->getMock(
            'Magento\Framework\App\Config', ['getValue'], [], '', false
        );

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->block = $objectManagerHelper->getObject(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo',
            [
                'customerDataFactory' => $customerDataFactory,
                'dateTime' => $dateTime,
                'customerLog' => $this->customerLog,
                'localeDate' => $this->localeDate,
                'scopeConfig' => $this->scopeConfig,
                'backendSession' => $backendSession,
            ]
        );
    }

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

        $this->assertEquals(
            $this->defaultTimezone, $this->block->getStoreLastLoginDateTimezone()
        );
    }

    /**
     * @param string $status
     * @param string|null $lastLoginAt
     * @param string|null $lastVisitAt
     * @param string|null $lastLogoutAt
     * @dataProvider getCurrentStatusDataProvider
     */
    public function testGetCurrentStatus($status, $lastLoginAt, $lastVisitAt, $lastLogoutAt)
    {
        $this->customerLog->expects($this->any())->method('getLastLoginAt')->willReturn($lastLoginAt);
        $this->customerLog->expects($this->any())->method('getLastVisitAt')->willReturn($lastVisitAt);
        $this->customerLog->expects($this->any())->method('getLastLogoutAt')->willReturn($lastLogoutAt);

        $this->assertEquals($status, $this->block->getCurrentStatus());
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
            ['Online', '2015-03-04 11:00:00', '2015-03-04 11:45:00', null]
        ];
    }

    /**
     * @param string $result
     * @param string|null $lastLoginAt
     * @dataProvider getLastLoginDateDataProvider
     */
    public function testGetLastLoginDate($result, $lastLoginAt)
    {
        $this->customerLog->expects($this->once())->method('getLastLoginAt')->willReturn($lastLoginAt);
        $this->localeDate->expects($this->any())->method('formatDate')->willReturn($lastLoginAt);

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
     */
    public function testGetStoreLastLoginDate($result, $lastLoginAt)
    {
        $this->customerLog->expects($this->once())->method('getLastLoginAt')->willReturn($lastLoginAt);

        $this->localeDate->expects($this->any())->method('scopeDate')->will($this->returnValue($lastLoginAt));
        $this->localeDate->expects($this->any())->method('formatDate')->willReturn($lastLoginAt);

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
}
