<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Paypal\Model\SmartButtonConfig;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for smart button config
 */
class SmartButtonConfigTest extends TestCase
{
    /**
     * @var SmartButtonConfig
     */
    private $model;

    /**
     * @var MockObject
     */
    private $localeResolverMock;

    /**
     * @var MockObject
     */
    private $configMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->localeResolverMock   = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->configMock           = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ScopeConfigInterface|MockObject $scopeConfigMock */
        $scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $scopeConfigMock->method('isSetFlag')
            ->willReturn(true);

        /** @var ConfigFactory|MockObject $configFactoryMock */
        $configFactoryMock = $this->getMockBuilder(ConfigFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $configFactoryMock->expects($this->any())->method('create')->willReturn($this->configMock);

        $sdkUrl = $this->createMock(\Magento\Paypal\Model\SdkUrl::class);
        $sdkUrl->method('getUrl')->willReturn('http://mock.url');

        $this->model = new SmartButtonConfig(
            $this->localeResolverMock,
            $configFactoryMock,
            $scopeConfigMock,
            $sdkUrl,
            $this->configMock,
            $this->getDefaultStyles()
        );
    }

    /**
     * Tests config.
     *
     * @param string $page
     * @param string $locale
     * @param bool $isCustomize
     * @param string $layout
     * @param string $shape
     * @param string $label
     * @param string $color
     * @param string $installmentPeriod
     * @param string $installmentPeriodLocale
     * @param array $expected
     * @dataProvider getConfigDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function testGetConfig(
        string $page,
        string $locale,
        bool $isCustomize,
        string $layout,
        string $shape,
        string $label,
        string $color,
        string $installmentPeriod,
        string $installmentPeriodLocale,
        array $expected = []
    ) {
        $this->localeResolverMock->method('getLocale')->willReturn($locale);
        $this->configMock->method('getValue')->willReturnMap(
            [
                ["{$page}_page_button_customize", null, $isCustomize],
                ["{$page}_page_button_layout", null, $layout],
                ["{$page}_page_button_color", null, $color],
                ["{$page}_page_button_shape", null, $shape],
                ["{$page}_page_button_label", null, $label],
                [
                    $page . '_page_button_' . $installmentPeriodLocale . '_installment_period',
                    null,
                    $installmentPeriod
                ]
            ]
        );

        self::assertEquals($expected, $this->model->getConfig($page));
    }

    /**
     * Get config data provider
     *
     * @return array
     */
    public static function getConfigDataProvider()
    {
        return include __DIR__ . '/_files/expected_style_config.php';
    }

    /**
     * Get default styles
     *
     * @return array
     */
    private function getDefaultStyles()
    {
        return include __DIR__ . '/_files/default_styles.php';
    }
}
