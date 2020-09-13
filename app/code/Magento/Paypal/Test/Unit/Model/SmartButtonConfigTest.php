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
        $this->localeResolverMock = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ScopeConfigInterface|MockObject $scopeConfigMock */
        $scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $scopeConfigMock->method('isSetFlag')
            ->willReturn(true);

        /** @var ConfigFactory|MockObject $configFactoryMock */
        $configFactoryMock = $this->getMockBuilder(ConfigFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $configFactoryMock->expects($this->once())->method('create')->willReturn($this->configMock);

        /** @var Store|MockObject $storeMock */
        $storeMock = $this->createMock(Store::class);
        $storeMock->method('getBaseCurrencyCode')
            ->willReturn('USD');

        /** @var StoreManagerInterface|MockObject $storeManagerMock */
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManagerMock->method('getStore')
            ->willReturn($storeMock);

        $this->model = new SmartButtonConfig(
            $this->localeResolverMock,
            $configFactoryMock,
            $scopeConfigMock,
            $storeManagerMock,
            $this->getDefaultStyles(),
            $this->getDisallowedFundingMap(),
            $this->getUnsupportedPaymentMethods()
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
        string $shape,
        string $label,
        string $color,
        string $installmentPeriodLabel,
        string $installmentPeriodLocale,
        bool $isPaypalGuestCheckoutEnabled,
        array $expected = []
    ) {
        $this->localeResolverMock->method('getLocale')->willReturn($locale);
        $this->configMock->method('getValue')->willReturnMap(
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
                ["{$page}_page_button_color", null, $color],
                ["{$page}_page_button_shape", null, $shape],
                ["{$page}_page_button_label", null, $label],
                ['sandbox_client_id', null, 'sb'],
                ['merchant_id', null, 'merchant'],
                [
                    'solution_type',
                    null,
                    $isPaypalGuestCheckoutEnabled ? Config::EC_SOLUTION_TYPE_SOLE : Config::EC_SOLUTION_TYPE_MARK
                ],
                ['sandbox_flag', null, true],
                ['paymentAction', null, 'Authorization'],
                ['disable_funding_options', null, $disallowedFundings],
                ["{$page}_page_button_customize", null, $isCustomize],
                ["{$page}_page_button_layout", null, $layout],
                ["{$page}_page_button_color", null, $color],
                ["{$page}_page_button_shape", null, $shape],
                ["{$page}_page_button_label", null, $label],
                [
                    $page . '_page_button_' . $installmentPeriodLocale . '_installment_period',
                    null,
                    $installmentPeriodLabel
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
     * Get disallowed funding map
     *
     * @return array
     */
    private function getDisallowedFundingMap()
    {
        return include __DIR__ . '/_files/disallowed_funding_map.php';
    }

    /**
     * Get unsupported payment methods
     *
     * @return array
     */
    private function getUnsupportedPaymentMethods()
    {
        return include __DIR__ . '/_files/unsupported_payment_methods.php';
    }
}
