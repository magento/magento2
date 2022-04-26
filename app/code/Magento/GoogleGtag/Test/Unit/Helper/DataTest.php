<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GoogleGtag\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GoogleGtag\Helper\Data as HelperData;
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
        $testMeasurementId,
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
                [HelperData::XML_PATH_MEASUREMENT_ID, ScopeInterface::SCOPE_STORE, null, $testMeasurementId]
            ]);
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(HelperData::XML_PATH_ACTIVE, ScopeInterface::SCOPE_STORE)
            ->willReturn($flagGaActive);
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
            [true, 'G-1234', 'G-1234', true],
            [false, 'G-1234', 'G-1234', false]
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
    public function testGetAccountId($testMeasurementId, $result): void
    {
        $this->scopeConfigMock
            ->method('getValue')
            ->willReturnMap([
                [HelperData::XML_PATH_MEASUREMENT_ID, ScopeInterface::SCOPE_STORE, null, $testMeasurementId]
            ]);
        $this->assertEquals($result, $this->helper->getAccountId());
    }

    /**
     * @return array
     */
    public function dataProviderForTestIsActive(): array
    {
        return [
            [true, 'AW-1234', true],
            [true, 'conversionId', true],
            [true, '', false],
            [false, '', false]
        ];
    }

    /**
     * @param bool $isActive
     * @param string $returnConfigValue
     * @param bool $returnValue
     *
     * @return void
     * @dataProvider dataProviderForTestIsActive
     */
    public function testIsGoogleAdwordsActive($isActive, $returnConfigValue, $returnValue): void
    {
        $this->scopeConfigMock->expects(
            $this->any()
        )->method(
            'isSetFlag'
        )->with(
            HelperData::XML_PATH_ADWORD_ACTIVE
        )->willReturn(
            $isActive
        );
        $this->scopeConfigMock->method('getValue')->with($this->isType('string'))->willReturnCallback(
            function () use ($returnConfigValue) {
                return $returnConfigValue;
            }
        );

        $this->assertEquals($returnValue, $this->helper->isGoogleAdwordsActive());
    }

    /**
     * @return array
     */
    public function dataProviderForTestStoreConfig(): array
    {
        return [
            ['getConversionId', HelperData::XML_PATH_CONVERSION_ID, 'AW-123'],
            ['getConversionLabel', HelperData::XML_PATH_CONVERSION_LABEL, 'Label']
        ];
    }

    /**
     * @param string $method
     * @param string $xmlPath
     * @param string $returnValue
     *
     * @return void
     * @dataProvider dataProviderForTestStoreConfig
     */
    public function testGetStoreConfigValue($method, $xmlPath, $returnValue): void
    {
        $this->scopeConfigMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            $xmlPath
        )->willReturn(
            $returnValue
        );

        $this->assertEquals($returnValue, $this->helper->{$method}());
    }

    /**
     * Data provider for testGetAccountId()
     *
     * @return array
     */
    public function dataGetAccountId(): array
    {
        return [
            ['G-1234', 'G-1234']
        ];
    }
}
