<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GoogleAnalytics\Test\Unit\Helper;

use Magento\GoogleAnalytics\Helper\Data as HelperData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

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
     * @return void
     * @dataProvider gaDataProvider
     */
    public function testIsGoogleAnalyticsAvailable($value, $flag): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(HelperData::XML_PATH_ACCOUNT, ScopeInterface::SCOPE_STORE)
            ->willReturn($value);

        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(HelperData::XML_PATH_ACTIVE, ScopeInterface::SCOPE_STORE)
            ->willReturn($flag);

        $this->assertEquals(($value && $flag), $this->helper->isGoogleAnalyticsAvailable());
    }

    /**
     * Data provider for isGoogleAnalyticsAvailable()
     *
     * @return array
     */
    public function gaDataProvider(): array
    {
        return [
            ['GA-XXXX', true],
            ['GA-XXXX', false],
            ['', true]
        ];
    }

    /**
     * Test for isAnonymizedIpActive()
     *
     * @return void
     * @dataProvider yesNoDataProvider
     */
    public function testIsAnonymizedIpActive($value): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(HelperData::XML_PATH_ANONYMIZE, ScopeInterface::SCOPE_STORE)
            ->willReturn($value);
        $this->assertEquals((bool) $value, $this->helper->isAnonymizedIpActive());
    }

    /**
     * Data provider for isAnonymizedIpActive()
     *
     * @return array
     */
    public function yesNoDataProvider(): array
    {
        return [
            ['Yes' => '1'],
            ['No' => '0']
        ];
    }
}
