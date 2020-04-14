<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\SmartButtonConfig;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Paypal\Model\ConfigFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for smart button config
 */
class SmartButtonConfigTest extends TestCase
{
    /**
     * @var \Magento\Paypal\Model\SmartButtonConfig
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
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->localeResolverMock = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ScopeConfigInterface|MockObject $scopeConfigMock */
        $scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $scopeConfigMock->method('isSetFlag')
            ->willReturn(true);

        /** @var MockObject $configFactoryMock */
        $configFactoryMock = $this->getMockBuilder(ConfigFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $configFactoryMock->expects($this->once())->method('create')->willReturn($this->configMock);
        $this->model = new SmartButtonConfig(
            $this->localeResolverMock,
            $configFactoryMock,
            $scopeConfigMock,
            $this->getDefaultStyles(),
            $this->getAllowedFundings()
        );
    }

    /**
     * Tests config.
     *
     * @param string $page
     * @param string $locale
     * @param bool $isCustomize
     * @param string $disallowedFundings
     * @param string $layout
     * @param string $size
     * @param string $shape
     * @param string $label
     * @param string $color
     * @param string $installmentPeriodLabel
     * @param string $installmentPeriodLocale
     * @param string $isPaypalGuestCheckoutEnabled
     * @param array $expected
     * @dataProvider getConfigDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function testGetConfig(
        string $page,
        string $locale,
        bool $isCustomize,
        ?string $disallowedFundings,
        string $layout,
        string $size,
        string $shape,
        string $label,
        string $color,
        string $installmentPeriodLabel,
        string $installmentPeriodLocale,
        string $isPaypalGuestCheckoutEnabled,
        array $expected = []
    ) {
        $this->localeResolverMock->method('getLocale')->willReturn($locale);
        $this->configMock->method('getValue')->will(
            $this->returnValueMap(
                [
                    ['merchant_id', null, 'merchant'],
                    [
                        'solution_type',
                        null,
                        $isPaypalGuestCheckoutEnabled ? Config::EC_SOLUTION_TYPE_SOLE : Config::EC_SOLUTION_TYPE_MARK
                    ],
                    ['sandbox_flag', null, true],
                    ['disable_funding_options', null, $disallowedFundings],
                    ["{$page}_page_button_customize", null, $isCustomize],
                    ["{$page}_page_button_layout", null, $layout],
                    ["{$page}_page_button_size", null, $size],
                    ["{$page}_page_button_color", null, $color],
                    ["{$page}_page_button_shape", null, $shape],
                    ["{$page}_page_button_label", null, $label],
                    [
                        $page . '_page_button_' . $installmentPeriodLocale . '_installment_period',
                        null,
                        $installmentPeriodLabel
                    ]
                ]
            )
        );

        self::assertEquals($expected, $this->model->getConfig($page));
    }

    /**
     * Get config data provider
     *
     * @return array
     */
    public function getConfigDataProvider()
    {
        return include __DIR__ . '/_files/expected_config.php';
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

    /**
     * Get allowed fundings
     *
     * @return array
     */
    private function getAllowedFundings()
    {
        return include __DIR__ . '/_files/allowed_fundings.php';
    }
}
