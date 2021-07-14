<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GoogleAnalytics\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GoogleAnalytics\Helper\Data as HelperData;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\GoogleAnalytics\Helper\Data
 */
class DataTest extends TestCase
{
    /**
     * @var HelperData
     */
    private $helper;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->setMethods(['getValue', 'isSetFlag'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->helper = $objectManager->getObject(
            HelperData::class,
            [
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * Test for isGoogleAnalyticsAvailable()
     *
     * @param bool $flagGaActive
     * @param int $testAccountType
     * @param string $testMeasurementId
     * @param string $testTrackingId
     * @param bool $testIsGA4Account
     * @param string $testAccountId
     * @param bool $result
     * @return void
     * @dataProvider gaDataProvider
     */
    public function testIsGoogleAnalyticsAvailable(
        $flagGaActive,
        $testAccountType,
        $testMeasurementId,
        $testTrackingId,
        $testIsGA4Account,
        $testAccountId,
        $result
    ): void {
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(HelperData::XML_PATH_ACTIVE, ScopeInterface::SCOPE_STORE)
            ->willReturn($flagGaActive);
        $this->scopeConfigMock
            ->method('getValue')
            ->willReturnMap([
                [HelperData::XML_PATH_ACCOUNT_TYPE, ScopeInterface::SCOPE_STORE, null, $testAccountType],
                [HelperData::XML_PATH_MEASUREMENT_ID, ScopeInterface::SCOPE_STORE, null, $testMeasurementId],
                [HelperData::XML_PATH_TRACKING_ID, ScopeInterface::SCOPE_STORE, null, $testTrackingId]
            ]);
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(HelperData::XML_PATH_ACTIVE, ScopeInterface::SCOPE_STORE)
            ->willReturn($flagGaActive);
        $this->assertEquals($testIsGA4Account, $this->helper->isGoogleAnalytics4Account());
        $this->assertEquals($testAccountId, $this->helper->getAccountId());
        $this->assertEquals($result, $this->helper->isGoogleAnalyticsAvailable());
    }

    /**
     * Data provider for testIsGoogleAnalyticsAvailable()
     *
     * @return array
     */
    public function gaDataProvider(): array
    {
        return [
            [true, 0, 'G-1234', 'UA-1234', true, 'G-1234', true],
            [true, 1, 'G-1234', 'UA-1234', false, 'UA-1234', true],
            [false, 0, 'G-1234', 'UA-1234', true, 'G-1234', false],
            [false, 1, 'G-1234', 'UA-1234', false, 'UA-1234', false],
        ];
    }

    /**
     * Test for isAnonymizedIpActive()
     * @param int|string $testAccountType
     * @param string $testMeasurementId
     * @param string $testTrackingId
     * @param int|string $testPathAnon
     * @param bool $testIsGA4Account
     * @param bool $result
     * @return void
     * @dataProvider isAnonDataProvider
     * //$testAccountType(int), $testMeasurementId(string), $testTrackingId(string), $testPathAnon(int), $testIsGA4Account(bool), $result(bool)
     */
    public function testIsAnonymizedIpActive($testAccountType, $testMeasurementId, $testTrackingId, $testPathAnon, $testIsGA4Account, $result): void
    {
        $test = $this->scopeConfigMock
            ->method('getValue')
            ->willReturnMap([
                [HelperData::ACCOUNT_TYPE_GOOGLE_ANALYTICS4, null, null, 0],
                [HelperData::ACCOUNT_TYPE_UNIVERSAL_ANALYTICS, null, null, 1],
                [HelperData::XML_PATH_ACCOUNT_TYPE, ScopeInterface::SCOPE_STORE, null, $testAccountType],
                [HelperData::XML_PATH_MEASUREMENT_ID, ScopeInterface::SCOPE_STORE, null, $testMeasurementId],
                [HelperData::XML_PATH_TRACKING_ID, ScopeInterface::SCOPE_STORE, null, $testTrackingId],
                [HelperData::XML_PATH_ANONYMIZE, ScopeInterface::SCOPE_STORE, null, $testPathAnon]
            ]);
        $this->assertEquals($testIsGA4Account, $this->helper->isGoogleAnalytics4Account());
        $this->assertEquals($result, $this->helper->isAnonymizedIpActive());
    }

    /**
     * Data provider for testIsAnonymizedIpActive()
     *
     * @return array
     */
    public function isAnonDataProvider(): array
    {
        return [
            ['testAccountType' => 0, 'testMeasurementId' => 'G-1234', 'testTrackingId' => 'UA-1234', 'testPathAnon' => 0, 'testIsGA4Account' => true, 'result' => true],
            ['testAccountType' => 1, 'testMeasurementId' => 'G-1234', 'testTrackingId' => 'UA-1234', 'testPathAnon' => 0, 'testIsGA4Account' => false, 'result' => false],
            ['testAccountType' => 1, 'testMeasurementId' => 'G-1234', 'testTrackingId' => 'UA-1234', 'testPathAnon' => 1, 'testIsGA4Account' => false, 'result' => true],
        ];
    }

    /**
     * Test for isGoogleAnalytics4Account()
     *
     * @param int $accountType
     * @param bool $result
     * @return void
     * @dataProvider googleAnalytics4AccountTypeData
     */
    public function testisGoogleAnalytics4Account($accountType, $result): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(HelperData::XML_PATH_ACCOUNT_TYPE, ScopeInterface::SCOPE_STORE)
            ->willReturn($accountType);
        $this->assertEquals($result, $this->helper->isGoogleAnalytics4Account());
    }

    /**
     * Data provider for testisGoogleAnalytics4Account()
     *
     * @return array
     */
    public function googleAnalytics4AccountTypeData(): array
    {
        return [
            ['accountType' => 0, 'result' => true],
            ['accountType' => 1, 'result' => false],
        ];
    }
    /**
     * Test for isUniversalAnalyticsAccount()
     * @param int $accountType
     * @param bool $result
     * @return void
     * @dataProvider universalAnalyticsAccountTypeData
     */
    public function testIsUniversalAnalyticsAccount($accountType, $result): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(HelperData::XML_PATH_ACCOUNT_TYPE, ScopeInterface::SCOPE_STORE)
            ->willReturn($accountType);
        $this->assertEquals($result, $this->helper->isUniversalAnalyticsAccount());
    }

    /**
     * Data provider for testIsUniversalAnalyticsAccount
     *
     * @return array
     */
    public function universalAnalyticsAccountTypeData(): array
    {
        return [
            ['accountType' => 0, 'result' => false],
            ['accountType' => 1, 'result' => true],
        ];
    }

    /**
     * Test for getAccountId()
     * @param bool $testAccountType
     * @param bool $testIsGA4Account
     * @param string $testMeasurementId
     * @param string $testTrackingId
     * @param string $result
     * @return void
     * @dataProvider dataGetAccountId
     */
    public function testGetAccountId($testAccountType, $testIsGA4Account, $testMeasurementId, $testTrackingId, $result): void
    {
        $this->scopeConfigMock
            ->method('getValue')
            ->willReturnMap([
                [HelperData::XML_PATH_ACCOUNT_TYPE, ScopeInterface::SCOPE_STORE, null, $testAccountType],
                [HelperData::XML_PATH_MEASUREMENT_ID, ScopeInterface::SCOPE_STORE, null, $testMeasurementId],
                [HelperData::XML_PATH_TRACKING_ID, ScopeInterface::SCOPE_STORE, null, $testTrackingId]
            ]);
        $this->assertEquals($testIsGA4Account, $this->helper->isGoogleAnalytics4Account());
        $this->assertEquals($result, $this->helper->getAccountId());
    }

    /**
     * Data provider for testGetAccountId()
     *
     * @return array
     */
    public function dataGetAccountId(): array
    {
        return [
            [0, true, 'G-1234', 'UA-1234', 'G-1234'],
            [1, false, 'G-1234', 'UA-1234', 'UA-1234']
        ];
    }
}
