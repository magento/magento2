<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Plugin;

use Magento\Theme\Model\View\Design;

class LocaleEmulator
{
    /**
     * @var Design
     */
    private $design;

    public function __construct(Design $design)
    {
        $this->design = $design;
    }

    /**
     * Set default design theme
     *
     * @param \Magento\Config\Console\Command\LocaleEmulator $subject
     * @param callable $proceed
     * @param callable $callback
     * @param string|null $locale
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundEmulate(
        \Magento\Config\Console\Command\LocaleEmulator $subject,
        callable $proceed,
        callable $callback,
        ?string $locale = null
    ): void {
        $initialTheme = $this->design->getDesignTheme();
        $this->design->setDefaultDesignTheme();
        try {
            $proceed($callback, $locale);
        } finally {
            if ($initialTheme) {
                $this->design->setDesignTheme($initialTheme);
            }
        }
    }
}
