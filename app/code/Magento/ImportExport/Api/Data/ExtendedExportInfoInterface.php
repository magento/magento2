<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Api\Data;

/**
 * Extended export interface for implementation of Skipped Attributes which are missing from the basic interface
 *
 * @api
 */
interface ExtendedExportInfoInterface extends ExportInfoInterface
{
    /**
     * Returns skipped attributes
     *
     * @return string[]|null
     */
    public function getSkipAttr(): ?array;

    /**
     * Set skipped attributes
     *
     * @param string[] $skipAttr
     * @return void
     */
    public function setSkipAttr(array $skipAttr): void;

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
