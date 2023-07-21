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
class SdkUrlTest extends TestCase
{
    /**
     * @var \Magento\Paypal\Model\SdkUrl
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
        $configFactoryMock->expects($this->any())->method('create')->willReturn($this->configMock);

        /** @var Store|MockObject $storeMock */
        $storeMock = $this->createMock(Store::class);
        $storeMock->method('getBaseCurrencyCode')
            ->willReturn('USD');

        /** @var StoreManagerInterface|MockObject $storeManagerMock */
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManagerMock->method('getStore')
            ->willReturn($storeMock);

        $this->model = new \Magento\Paypal\Model\SdkUrl(
            $this->localeResolverMock,
            $configFactoryMock,
            $scopeConfigMock,
            $storeManagerMock,
            $this->getDisallowedFundingMap(),
            $this->getUnsupportedPaymentMethods(),
            $this->getSupportedPaymentMethods()
        );
    }

    /**
     * Tests config.
     *
     * @param string $locale
     * @param string $intent
     * @param string|null $disallowedFunding
     * @param bool $isBuyerCountryEnabled
     * @param bool $isPaypalGuestCheckoutEnabled
     * @param array $expected
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig(
        string $locale,
        string $intent,
        ?string $disallowedFunding,
        bool $isBuyerCountryEnabled,
        bool $isPaypalGuestCheckoutEnabled,
        array $expected
    ) {
        $this->localeResolverMock->method('getLocale')->willReturn($locale);
        $this->configMock->method('getValue')->willReturnMap(
            [
                ['merchant_id', null, 'merchant'],
                ['sandbox_client_id', null, 'sb'],
                ['sandbox_flag', null, true],
                ['buyer_country', null, $isBuyerCountryEnabled ? 'US' : ''],
                ['disable_funding_options', null, $disallowedFunding],
                ['paymentAction', null, $intent],
                ['in_context', null, true],
                [
                    'solution_type',
                    null,
                    $isPaypalGuestCheckoutEnabled ? Config::EC_SOLUTION_TYPE_SOLE : Config::EC_SOLUTION_TYPE_MARK
                ],
            ]
        );

        $this->configMock->method('getPayLaterConfigValue')
            ->with('experience_active')
            ->willReturn(true);

        self::assertEquals($expected['sdkUrl'], $this->model->getUrl());
    }

    /**
     * Get config data provider
     *
     * @return array
     */
    public function getConfigDataProvider()
    {
        return include __DIR__ . '/_files/expected_url_config.php';
    }

    /**
     * Get disallowed funding map
     * See app/code/Magento/Paypal/etc/frontend/di.xml
     *
     * @return array
     */
    private function getDisallowedFundingMap()
    {
        return [
            "CREDIT" => 'credit',
            "VENMO" => 'venmo',
            "CARD" => 'card',
            "ELV" => 'sepa'
        ];
    }

    /**
     * Get unsupported payment methods
     * See app/code/Magento/Paypal/etc/frontend/di.xml
     *
     * @return array
     */
    private function getUnsupportedPaymentMethods()
    {
        return [
            'bancontact' => 'bancontact',
            'eps' => 'eps',
            'giropay' => 'giropay',
            'ideal' => 'ideal',
            'mybank' => 'mybank',
            'p24' => 'p24',
            'sofort' => 'sofort',
        ];
    }

    /**
     * Get supported payment methods
     * See app/code/Magento/Paypal/etc/frontend/di.xml
     *
     * @return string[]
     */
    private function getSupportedPaymentMethods()
    {
        return [
            'venmo'=> 'venmo',
            'paylater'=> 'paylater',
        ];
    }
}
