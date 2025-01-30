<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GoogleGtag\Test\Unit\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GoogleGtag\Model\Config\GtagConfig as GtagConfig;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\GoogleAnalytics\Model\Config\GtagConfig
 */
class GtagConfigTest extends TestCase
{
    /**
     * Config paths for using throughout the code
     */
    private const XML_PATH_ACTIVE = 'google/gtag/analytics4/active';

    private const XML_PATH_MEASUREMENT_ID = 'google/gtag/analytics4/measurement_id';

    /**
     * Google AdWords conversion src
     */
    private const GTAG_GLOBAL_SITE_TAG_SRC = 'https://www.googletagmanager.com/gtag/js?id=';

    /**#@+
     * Google AdWords config data
     */
    private const XML_PATH_ADWORD_ACTIVE = 'google/gtag/adwords/active';

    private const XML_PATH_CONVERSION_ID = 'google/gtag/adwords/conversion_id';

    private const XML_PATH_CONVERSION_LABEL = 'google/gtag/adwords/conversion_label';

    /**
     * @var GtagConfig
     */
    private $gtagConfig;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private static $scopeConfigMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        self::$scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->onlyMethods(['getValue', 'isSetFlag'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->gtagConfig = $objectManager->getObject(
            GtagConfig::class,
            [
                'scopeConfig' => self::$scopeConfigMock
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
        self::$scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(self::XML_PATH_ACTIVE, ScopeInterface::SCOPE_STORE)
            ->willReturn($flagGaActive);
        self::$scopeConfigMock
            ->method('getValue')
            ->willReturnMap([
                [self::XML_PATH_MEASUREMENT_ID, ScopeInterface::SCOPE_STORE, null, $testMeasurementId]
            ]);
        self::$scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(self::XML_PATH_ACTIVE, ScopeInterface::SCOPE_STORE)
            ->willReturn($flagGaActive);
        $this->assertEquals($testAccountId, $this->gtagConfig->getMeasurementId());
        $this->assertEquals($result, $this->gtagConfig->isGoogleAnalyticsAvailable());
    }

    /**
     * Data provider for testIsGoogleAnalyticsAvailable()
     *
     * @return array
     */
    public static function gaDataProvider(): array
    {
        return [
            [true, 'G-1234', 'G-1234', true],
            [false, 'G-1234', 'G-1234', false]
        ];
    }

    /**
     * Test for getMeasurementId()
     * @param bool $testAccountType
     * @param bool $testIsGA4Account
     * @param string $testMeasurementId
     * @param string $testTrackingId
     * @param string $result
     * @return void
     * @dataProvider dataGetMeasurementId
     */
    public function testGetMeasurementId($testMeasurementId, $result): void
    {
        self::$scopeConfigMock
            ->method('getValue')
            ->willReturnMap([
                [self::XML_PATH_MEASUREMENT_ID, ScopeInterface::SCOPE_STORE, null, $testMeasurementId]
            ]);
        $this->assertEquals($result, $this->gtagConfig->getMeasurementId());
    }

    /**
     * @return array
     */
    public static function dataProviderForTestIsActive(): array
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
        self::$scopeConfigMock->expects(
            $this->any()
        )->method(
            'isSetFlag'
        )->with(
            self::XML_PATH_ADWORD_ACTIVE
        )->willReturn(
            $isActive
        );
        self::$scopeConfigMock->method('getValue')->with($this->isType('string'))->willReturnCallback(
            function () use ($returnConfigValue) {
                return $returnConfigValue;
            }
        );

        $this->assertEquals($returnValue, $this->gtagConfig->isGoogleAdwordsActive());
    }

    /**
     * @return array
     */
    public static function dataProviderForTestStoreConfig(): array
    {
        return [
            ['getConversionId', self::XML_PATH_CONVERSION_ID, 'AW-123'],
            ['getConversionLabel', self::XML_PATH_CONVERSION_LABEL, 'Label']
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
        self::$scopeConfigMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            $xmlPath
        )->willReturn(
            $returnValue
        );

        $this->assertEquals($returnValue, $this->gtagConfig->{$method}());
    }

    /**
     * Data provider for testGetMeasurementId()
     *
     * @return array
     */
    public static function dataGetMeasurementId(): array
    {
        return [
            ['G-1234', 'G-1234']
        ];
    }
}
