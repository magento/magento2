<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportCsvApi\Api;

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
     * @param \Magento\ImportCsvApi\Api\Data\SourceDataInterface $source Describes how to retrieve data from data source
     * @return mixed
     * @throws ValidationException
     */
    public function execute(
        \Magento\ImportCsvApi\Api\Data\SourceDataInterface $source
    ): array;
}
