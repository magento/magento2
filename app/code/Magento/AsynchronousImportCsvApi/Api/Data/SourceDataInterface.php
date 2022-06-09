<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousImportCsvApi\Api\Data;

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
    public const IMPORT_FIELD_SEPARATOR = 'importFieldSeparator';
    public const IMPORT_MULTIPLE_VALUE_SEPARATOR = 'importMultipleValueSeparator';
    public const IMPORT_EMPTY_ATTRIBUTE_VALUE_CONSTANT = 'importEmptyAttributeValueConstant';
    public const IMPORT_IMAGES_FILE_DIR = 'importImagesFileDir';

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

    /**
     *
     * @return string
     */
    public function getImportFieldSeparator(): string;

    /**
     *
     * @return string
     */
    public function getImportMultipleValueSeparator(): string;

    /**
     *
     * @return string
     */
    public function getImportEmptyAttributeValueConstant(): string;

    /**
     *
     * @return string
     */
    public function getImportImagesFileDir(): string;
}
