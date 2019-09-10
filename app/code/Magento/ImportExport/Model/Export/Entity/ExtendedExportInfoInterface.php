<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Export\Entity;

use Magento\ImportExport\Api\Data\ExportInfoInterface;

/**
 * Basic interface with data needed for export operation.
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
