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

/**
 * Class LocaleResolverTest
 */
class LocaleResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResolverInterface
     */
    private $resolver;

    /**
     * @var ExpressLocaleResolver
     */
    private $model;

    protected function setUp()
    {
        $this->resolver = $this->createMock(ResolverInterface::class);
        /** @var Config $config */
        $config = $this->createMock(Config::class);
        $config->method('getValue')
            ->with('supported_locales')
            ->willReturn('zh_CN,zh_HK,zh_TW,fr_FR');

        /** @var ConfigFactory $configFactory */
        $configFactory = $this->createPartialMock(ConfigFactory::class, ['create']);
        $configFactory->method('create')->willReturn($config);

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
}
