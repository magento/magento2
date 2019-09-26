<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Api\Data;

/**
 * Extended export interface for implementation of Skipped Attributes which are missing from the basic interface
 */
interface ExtendedExportInfoInterface extends ExportInfoInterface
{
    /**
     * Returns skipped attributes
     *
     * @return mixed
     */
    public function getSkipAttr();

    /**
     * Set skipped attributes
     *
     * @param string $skipAttr
     * @return mixed
     */
    public function setSkipAttr($skipAttr);
}
