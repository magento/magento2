<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Report;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Error report generator interface
 *
 * @api
 */
interface ReportProcessorInterface
{
    /**
     * @param string $originalFileName
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param bool $writeOnlyErrorItems
     * @return string
     */
    public function createReport(
        $originalFileName,
        ProcessingErrorAggregatorInterface $errorAggregator,
        $writeOnlyErrorItems = false
    );
}
