<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousImportCsvApi\Api;

use Magento\AsynchronousImportCsvApi\Api\Data\SourceDataInterface;
use Magento\AsynchronousImportDataConvertingApi\Api\ApplyConvertingRulesException;
use Magento\AsynchronousImportDataExchangingApi\Api\ImportDataExchangeException;
use Magento\AsynchronousImportSourceDataRetrievingApi\Api\SourceDataRetrievingException;
use Magento\Framework\Validation\ValidationException;

/**
 * Start import operation
 *
 * @api
 */
interface StartImportInterface
{
    /**
     * Start import operation
     *
     * @param SourceDataInterface $source Describes how to retrieve data from data source
     * @return array
     * @throws ValidationException
     * @throws SourceDataRetrievingException
     * @throws ApplyConvertingRulesException
     * @throws ImportDataExchangeException
     */
    public function execute(
        SourceDataInterface $source
    ): array;
}
