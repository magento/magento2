<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Model;

use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\LocaleResolver;
use Magento\Framework\Locale\ResolverInterface;

/**
 * @covers \Magento\Braintree\Model\LocaleResolver
 */
class LocaleResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Testable Object
     *
     * @var LocaleResolver
     */
    private $localeResolver;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resolverMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->configMock = $this->createMock(Config::class);
        $this->resolverMock = $this->createMock(ResolverInterface::class);
        $this->localeResolver = new LocaleResolver($this->resolverMock, $this->configMock);
    }

    /**
     * Test getDefaultLocalePath method
     *
     * @return void
     */
    public function testGetDefaultLocalePath()
    {
        $expected = 'general/locale/code';
        $this->resolverMock->expects($this->once())->method('getDefaultLocalePath')->willReturn($expected);
        $actual = $this->localeResolver->getDefaultLocalePath();
        self::assertEquals($expected, $actual);
    }

    /**
     * Test setDefaultLocale method
     *
     * @return void
     */
    public function testSetDefaultLocale()
    {
        $defaultLocale = 'en_US';
        $this->resolverMock->expects($this->once())->method('setDefaultLocale')->with($defaultLocale);
        $this->localeResolver->setDefaultLocale($defaultLocale);
    }

    /**
     * Test getDefaultLocale method
     *
     * @return void
     */
    public function testGetDefaultLocale()
    {
        $expected = 'fr_FR';
        $this->resolverMock->expects($this->once())->method('getDefaultLocale')->willReturn($expected);
        $actual = $this->localeResolver->getDefaultLocale();
        self::assertEquals($expected, $actual);
    }

    /**
     * Test setLocale method
     *
     * @return void
     */
    public function testSetLocale()
    {
        $locale = 'en_GB';
        $this->resolverMock->expects($this->once())->method('setLocale')->with($locale);
        $this->localeResolver->setLocale($locale);
    }

    /**
     * Test getLocale method
     *
     * @param string $locale
     * @param string $expectedLocale
     * @dataProvider getLocaleDataProvider
     */
    public function testGetLocale(string $locale, string $expectedLocale)
    {
        $allowedLocales = 'en_US,en_GB,en_AU,da_DK,fr_FR,fr_CA,de_DE,zh_HK,it_IT,zh_CN,zh_TW,nl_NL';
        $this->resolverMock->method('getLocale')
            ->willReturn($locale);
        $this->configMock->method('getValue')
            ->with('supported_locales')
            ->willReturn($allowedLocales);
        $actual = $this->localeResolver->getLocale();

        self::assertEquals($expectedLocale, $actual);
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
     * Test emulate method
     *
     * @return void
     */
    public function testEmulate()
    {
        $scopeId = 12;
        $this->resolverMock->expects($this->once())->method('emulate')->with($scopeId);
        $this->localeResolver->emulate($scopeId);
    }

    /**
     * Test revert method
     *
     * @return void
     */
    public function testRevert()
    {
        $this->resolverMock->expects($this->once())->method('revert');
        $this->localeResolver->revert();
    }
}
