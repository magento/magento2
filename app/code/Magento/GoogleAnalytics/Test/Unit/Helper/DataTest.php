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
     * @param string $value
     * @param bool $flag
     * @param bool $result
     * @return void
     * @dataProvider gaDataProvider
     */
    public function testIsGoogleAnalyticsAvailable($value, $flag, $result): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(HelperData::XML_PATH_ACCOUNT_TYPE, ScopeInterface::SCOPE_STORE)
            ->willReturn($value);

        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(HelperData::XML_PATH_ACTIVE, ScopeInterface::SCOPE_STORE)
            ->willReturn($flag);

        $this->assertEquals($result, $this->helper->isGoogleAnalyticsAvailable());
    }

    /**
     * Data provider for isGoogleAnalyticsAvailable()
     *
     * @return array
     */
    public function gaDataProvider(): array
    {
        return [
            ['0', true, true],
            ['0', false, false],
            ['1', true, true],
            ['1', false, false]
        ];
    }

    /**
     * Test for isAnonymizedIpActive()
     *
     * @param string $value
     * @param bool $result
     * @return void
     * @dataProvider yesNoDataProvider
     */
    public function testIsAnonymizedIpActive($value, $result): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isGoogleAnalyticsAccount')
            ->with(HelperData::XML_PATH_ANONYMIZE, ScopeInterface::SCOPE_STORE)
            ->willReturn($value);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(HelperData::XML_PATH_ANONYMIZE, ScopeInterface::SCOPE_STORE)
            ->willReturn($value);
        $this->assertEquals($result, $this->helper->isAnonymizedIpActive());
    }

    /**
     * Data provider for isAnonymizedIpActive()
     *
     * @return array
     */
    public function yesNoDataProvider(): array
    {
        return [
            ['Yes' => '1', 'result' => true],
            ['No' => '0', 'result' => false]
        ];
    }
    /**
     * Test for getAccountType()
     *
     * @param int $value
     * @param bool $result
     * @return void
     * @dataProvider accountType
     */
    public function testGetAccountType($value, $result): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(HelperData::XML_PATH_ACCOUNT_TYPE, ScopeInterface::SCOPE_STORE)
            ->willReturn($value);
        $this->assertEquals($result, $this->helper->getAccountType());
    }

    /**
     * Data provider for getAccountType()
     *
     * @return array
     */
    public function accountType(): array
    {
        return [
            ['Google Analytics 4' => 0, 'result' => 0],
            ['Universal Analytics' => 1, 'result' => 1]
        ];
    }

    /**
     * Test for isGoogleAnalyticsAccount()
     *
     * @param int $value
     * @param bool $result
     * @return void
     * @dataProvider googleAnalyticsAccountType
     */
    public function testIsGoogleAnalyticsAccount($value, $result): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(HelperData::XML_PATH_ACCOUNT_TYPE, ScopeInterface::SCOPE_STORE)
            ->willReturn($value);
        $this->assertEquals($result, $this->helper->isGoogleAnalyticsAccount());
    }

    /**
     * Data provider for getAccountType()
     *
     * @return array
     */
    public function googleAnalyticsAccountType(): array
    {
        return [
            ['Google Analytics 4' => 0, 'result' => true],
            ['Universal Analytics' => 1, 'result' => false],
        ];
    }
    /**
     * Test for isUniversalAnalyticsAccount()
     *
     * @param int $value
     * @param bool $result
     * @return void
     * @dataProvider universalAnalyticsAccountType
     */
    public function testIsUniversalAnalyticsAccount($value, $result): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(HelperData::XML_PATH_ACCOUNT_TYPE, ScopeInterface::SCOPE_STORE)
            ->willReturn($value);
        $this->assertEquals($result, $this->helper->isUniversalAnalyticsAccount());
    }

    /**
     * Data provider for getAccountType()
     *
     * @return array
     */
    public function universalAnalyticsAccountType(): array
    {
        return [
            ['Google Analytics 4' => 0, 'result' => false],
            ['Universal Analytics' => 1, 'result' => true],
        ];
    }
}
