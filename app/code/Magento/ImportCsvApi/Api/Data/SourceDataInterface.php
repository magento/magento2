<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportCsvApi\Api\Data;

/**
 * Describes how to retrieve data from data source
 *
 * @api
 */
interface SourceDataInterface
{
    public const ENTITY = 'entity';
    public const BEHAVIOR = 'behavior';
    public const VALIDATION_STRATEGY = 'validationStrategy';
    public const ALLOWED_ERROR_COUNT = 'allowedErrorCount';

    /**
     *
     * @return string
     */
    public function getEntity(): string;

    /**
     *
     * @return string
     */
    public function getBehavior(): string;

    /**
     *
     * @return string
     */
    public function getValidationStrategy(): string;

    /**
     *
     * @return string
     */
    public function getAllowedErrorCount(): string;

}
