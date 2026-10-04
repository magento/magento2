<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Console\Command;

/**
 * Locale emulator for config set and show
 */
interface LocaleEmulatorInterface
{
    /**
     * Emulates given $locale during execution of $callback
     *
     * @param callable $callback
     * @param string|null $locale
     * @return mixed
     */
    public function emulate(callable $callback, ?string $locale = null): mixed;
}
