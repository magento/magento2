<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\TranslateInterface;
use Magento\ImportExport\Model\LocaleEmulator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LocaleEmulatorTest extends TestCase
{
    /**
     * @var TranslateInterface|MockObject
     */
    private $translate;

    /**
     * @var RendererInterface|MockObject
     */
    private $phraseRenderer;

    /**
     * @var ResolverInterface|MockObject
     */
    private $localeResolver;

    /**
     * @var ResolverInterface|MockObject
     */
    private $defaultLocaleResolver;

    /**
     * @var LocaleEmulator
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->translate = $this->getMockForAbstractClass(TranslateInterface::class);
        $this->phraseRenderer = $this->getMockForAbstractClass(RendererInterface::class);
        $this->localeResolver = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->defaultLocaleResolver = $this->getMockForAbstractClass(ResolverInterface::class);
        $this->model = new LocaleEmulator(
            $this->translate,
            $this->phraseRenderer,
            $this->localeResolver,
            $this->defaultLocaleResolver
        );
    }

    public function testEmulateWithSpecificLocale(): void
    {
        $initialLocale = 'en_US';
        $initialPhraseRenderer = Phrase::getRenderer();
        $locale = 'fr_FR';
        $mock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['assertPhraseRenderer'])
            ->getMock();
        $mock->expects($this->once())
            ->method('assertPhraseRenderer')
            ->willReturnCallback(
                fn () => $this->assertSame($this->phraseRenderer, Phrase::getRenderer())
            );
        $this->defaultLocaleResolver->expects($this->never())
            ->method('getLocale');
        $this->localeResolver->expects($this->once())
            ->method('getLocale')
            ->willReturn($initialLocale);
        $this->localeResolver->expects($this->exactly(2))
            ->method('setLocale')
            ->willReturnCallback(function ($arg1) use ($locale, $initialLocale) {
                if ($arg1 == $locale || $arg1 == $initialLocale) {
                    return null;
                }
            });
        $this->translate->expects($this->exactly(2))
            ->method('setLocale')
            ->willReturnCallback(function ($arg1) use ($locale, $initialLocale) {
                if ($arg1 == $locale || $arg1 == $initialLocale) {
                    return null;
                }
            });
        $this->translate->expects($this->exactly(2))
            ->method('loadData');
        $this->model->emulate($mock->assertPhraseRenderer(...), $locale);
        $this->assertSame($initialPhraseRenderer, Phrase::getRenderer());
    }

    public function testEmulateWithDefaultLocale(): void
    {
        $initialLocale = 'en_US';
        $initialPhraseRenderer = Phrase::getRenderer();
        $locale = 'fr_FR';
        $mock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['assertPhraseRenderer'])
            ->getMock();
        $mock->expects($this->once())
            ->method('assertPhraseRenderer')
            ->willReturnCallback(
                fn () => $this->assertSame($this->phraseRenderer, Phrase::getRenderer())
            );
        $this->defaultLocaleResolver->expects($this->once())
            ->method('getLocale')
            ->willReturn($locale);
        $this->localeResolver->expects($this->once())
            ->method('getLocale')
            ->willReturn($initialLocale);
        $this->localeResolver->expects($this->exactly(2))
            ->method('setLocale')
            ->willReturnCallback(function ($arg1) use ($locale, $initialLocale) {
                if ($arg1 == $locale || $arg1 == $initialLocale) {
                    return null;
                }
            });
        $this->translate->expects($this->exactly(2))
            ->method('setLocale')
            ->willReturnCallback(function ($arg1) use ($locale, $initialLocale) {
                if ($arg1 == $locale || $arg1 == $initialLocale) {
                    return null;
                }
            });
        $this->translate->expects($this->exactly(2))
            ->method('loadData');
        $this->model->emulate($mock->assertPhraseRenderer(...));
        $this->assertSame($initialPhraseRenderer, Phrase::getRenderer());
    }

    public function testEmulateWithException(): void
    {
        $exception = new \Exception('Oops! Something went wrong.');
        $this->expectExceptionObject($exception);
        $initialLocale = 'en_US';
        $initialPhraseRenderer = Phrase::getRenderer();
        $locale = 'fr_FR';
        $mock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['callbackThatThrowsException'])
            ->getMock();
        $mock->expects($this->once())
            ->method('callbackThatThrowsException')
            ->willThrowException($exception);
        $this->defaultLocaleResolver->expects($this->once())
            ->method('getLocale')
            ->willReturn($locale);
        $this->localeResolver->expects($this->once())
            ->method('getLocale')
            ->willReturn($initialLocale);
        $this->localeResolver->expects($this->exactly(2))
            ->method('setLocale')
            ->willReturnCallback(function ($arg1) use ($locale, $initialLocale) {
                if ($arg1 == $locale || $arg1 == $initialLocale) {
                    return null;
                }
            });
        $this->translate->expects($this->exactly(2))
            ->method('setLocale')
            ->willReturnCallback(function ($arg1) use ($locale, $initialLocale) {
                if ($arg1 == $locale || $arg1 == $initialLocale) {
                    return null;
                }
            });
        $this->translate->expects($this->exactly(2))
            ->method('loadData');
        $this->model->emulate($mock->callbackThatThrowsException(...));
        $this->assertSame($initialPhraseRenderer, Phrase::getRenderer());
    }
}
