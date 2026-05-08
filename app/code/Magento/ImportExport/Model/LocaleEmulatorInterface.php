<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model;

/**
 * Locale emulator for import and export
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
