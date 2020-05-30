<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Express;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Paypal\Model\Express\LocaleResolver as ExpressLocaleResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for PayPal express checkout resolver
 */
class LocaleResolverTest extends TestCase
{
    /**
     * @var MockObject|ResolverInterface
     */
    private $resolver;

    /**
     * @var ExpressLocaleResolver
     */
    private $model;

    /**
     * @var Config
     */
    private $config;

    protected function setUp(): void
    {
        $this->resolver = $this->getMockForAbstractClass(ResolverInterface::class);
        /** @var Config $config */
        $this->config = $this->createMock(Config::class);

        /** @var ConfigFactory $configFactory */
        $configFactory = $this->createPartialMock(ConfigFactory::class, ['create']);
        $configFactory->method('create')->willReturn($this->config);

        $this->model = new ExpressLocaleResolver($this->resolver, $configFactory);
    }

    /**
     * Tests retrieving locales for PayPal Express.
     *
     * @param string $locale
     * @param string $expectedLocale
     * @dataProvider getLocaleDataProvider
     */
    public function testGetLocale(string $locale, string $expectedLocale)
    {
        $this->resolver->method('getLocale')
            ->willReturn($locale);
        $this->config->method('getValue')->willReturnMap(
            
                [
                    ['in_context', null, false],
                    ['supported_locales', null, 'zh_CN,zh_HK,zh_TW,fr_FR'],
                ]
            
        );
        $this->assertEquals($expectedLocale, $this->model->getLocale());
    }

    /**
     * @return array
     */
    public function getLocaleDataProvider(): array
    {
        return [
            ['locale' => 'zh_Hans_CN', 'expectedLocale' => 'zh_CN'],
            ['locale' => 'zh_Hant_HK', 'expectedLocale' => 'zh_HK'],
            ['locale' => 'zh_Hant_TW', 'expectedLocale' => 'zh_TW'],
            ['locale' => 'fr_FR', 'expectedLocale' => 'fr_FR'],
            ['locale' => 'unknown', 'expectedLocale' => 'en_US'],
        ];
    }

    /**
     * Tests retrieving locales for PayPal Express Smart Buttons.
     *
     */
    public function testGetLocaleForSmartButtons()
    {
        $this->resolver->method('getLocale')
            ->willReturn('zh_Hans_CN');
        $this->config->method('getValue')->willReturnMap(
            
                [
                    ['in_context', null, true],
                    ['smart_buttons_supported_locales', null, 'zh_CN,zh_HK,zh_TW,fr_FR'],
                ]
            
        );
        $this->assertEquals('zh_CN', $this->model->getLocale());
    }
}
