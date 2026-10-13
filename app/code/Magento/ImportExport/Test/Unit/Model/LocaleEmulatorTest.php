<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TranslateInterface;
use Magento\ImportExport\Model\LocaleEmulator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LocaleEmulatorTest extends TestCase
{
    use MockCreationTrait;

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
        $this->translate = $this->createMock(TranslateInterface::class);
        $this->phraseRenderer = $this->createMock(RendererInterface::class);
        $this->localeResolver = $this->createMock(ResolverInterface::class);
        $this->defaultLocaleResolver = $this->createMock(ResolverInterface::class);
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
        $mock = $this->createPartialMockWithReflection(\stdClass::class, ['assertPhraseRenderer']);
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
        $mock = $this->createPartialMockWithReflection(\stdClass::class, ['assertPhraseRenderer']);
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
        $mock = $this->createPartialMockWithReflection(\stdClass::class, ['callbackThatThrowsException']);
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
