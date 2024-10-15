<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\TranslateInterface;

class LocaleEmulator implements LocaleEmulatorInterface
{
    /**
     * @var bool
     */
    private bool $isEmulating = false;

    /**
     * @param TranslateInterface $translate
     * @param RendererInterface $phraseRenderer
     * @param ResolverInterface $localeResolver
     * @param ResolverInterface $defaultLocaleResolver
     */
    public function __construct(
        private readonly TranslateInterface $translate,
        private readonly RendererInterface $phraseRenderer,
        private readonly ResolverInterface $localeResolver,
        private readonly ResolverInterface $defaultLocaleResolver
    ) {
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
            $result = $callback();
        } finally {
            Phrase::setRenderer($initialPhraseRenderer);
            $this->localeResolver->setLocale($initialLocale);
            $this->translate->setLocale($initialLocale);
            $this->translate->loadData();
            $this->isEmulating = false;
        }
        return $result;
    }
}
