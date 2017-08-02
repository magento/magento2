<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Report;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Error report generator interface
 * @since 2.0.0
 */
interface ReportProcessorInterface
{
    /**
     * @param string $originalFileName
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param bool $writeOnlyErrorItems
     * @return string
     * @since 2.0.0
     */
    public function createReport(
        $originalFileName,
        ProcessingErrorAggregatorInterface $errorAggregator,
        $writeOnlyErrorItems = false
    );
}
