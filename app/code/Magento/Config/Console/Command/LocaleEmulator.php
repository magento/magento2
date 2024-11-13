<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Console\Command;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\TranslateInterface;

/**
 * Locale emulator for config set and show
 */
class LocaleEmulator implements LocaleEmulatorInterface
{
    /**
     * @var bool
     */
    private bool $isEmulating = false;

    /**
     * @var TranslateInterface
     */
    private TranslateInterface $translate;

    /**
     * @var RendererInterface
     */
    private RendererInterface $phraseRenderer;

    /**
     * @var ResolverInterface
     */
    private ResolverInterface $localeResolver;

    /**
     * @var ResolverInterface
     */
    private ResolverInterface $defaultLocaleResolver;

    /**
     * @param TranslateInterface $translate
     * @param RendererInterface $phraseRenderer
     * @param ResolverInterface $localeResolver
     * @param ResolverInterface $defaultLocaleResolver
     */
    public function __construct(
        TranslateInterface $translate,
        RendererInterface $phraseRenderer,
        ResolverInterface $localeResolver,
        ResolverInterface $defaultLocaleResolver,
    ) {
        $this->translate = $translate;
        $this->phraseRenderer = $phraseRenderer;
        $this->localeResolver = $localeResolver;
        $this->defaultLocaleResolver = $defaultLocaleResolver;
    }

    /**
     * @inheritdoc
     */
    public function emulate(callable $callback, ?string $locale = null): mixed
    {
        if ($this->isEmulating) {
            return $callback();
        }
        $this->isEmulating = true;
        $locale ??= $this->defaultLocaleResolver->getLocale();
        $initialLocale = $this->localeResolver->getLocale();
        $initialPhraseRenderer = Phrase::getRenderer();
        Phrase::setRenderer($this->phraseRenderer);
        $this->localeResolver->setLocale($locale);
        $this->translate->setLocale($locale);
        $this->translate->loadData();
        try {
            return $callback();
        } finally {
            Phrase::setRenderer($initialPhraseRenderer);
            $this->localeResolver->setLocale($initialLocale);
            $this->translate->setLocale($initialLocale);
            $this->translate->loadData();
            $this->isEmulating = false;
        }
    }
}
