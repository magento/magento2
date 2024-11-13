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
            ->onlyMethods(['getValue', 'isSetFlag'])
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
            ->with(HelperData::XML_PATH_ACCOUNT, ScopeInterface::SCOPE_STORE)
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
    public static function gaDataProvider(): array
    {
        return [
            ['GA-XXXX', true, true],
            ['GA-XXXX', false, false],
            ['', true, false]
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
    public static function yesNoDataProvider(): array
    {
        return [
            ['value' => '1', 'result' => true],
            ['value' => '0', 'result' => false]
        ];
    }
}
