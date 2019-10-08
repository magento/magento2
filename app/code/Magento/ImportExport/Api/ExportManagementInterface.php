<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Api;

use Magento\ImportExport\Api\Data\ExportInfoInterface;

/**
 * Describes how to do export operation with data interface.
 * @api
 * @since 100.3.2
 */
interface ExportManagementInterface
{
    /**
     * Return export data.
     *
     * @param ExportInfoInterface $exportInfo
     * @return string
     * @since 100.3.2
     */
    public function export(ExportInfoInterface $exportInfo);
}
