<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Api\Data;

/**
 * Localized export interface for correct parsing values (like date) provided in admin ui locale.
 */
interface LocalizedExportInfoInterface extends ExtendedExportInfoInterface
{
    /**
     * Returns admin locale
     *
     * @return string|null
     */
    public function getLocale(): ?string;

    /**
     * Set admin locale
     *
     * @param string $locale
     * @return void
     */
    public function setLocale(string $locale): void;
}
