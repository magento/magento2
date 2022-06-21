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
 * @since 100.0.2
 */
interface SourceDataInterface
{
    public const ENTITY = 'entity';
    public const BEHAVIOR = 'behavior';
    public const VALIDATION_STRATEGY = 'validation_strategy';
    public const ALLOWED_ERROR_COUNT = 'allowed_error_count';

    /**
     * Get Entity
     *
     * @return string
     */
    public function getEntity(): string;

    /**
     * Get Behavior
     *
     * @return string
     */
    public function getBehavior(): string;

    /**
     * Get Validation Strategy
     *
     * @return string
     */
    public function getValidationStrategy(): string;

    /**
     * Get Allowed Error Count
     *
     * @return string
     */
    public function getAllowedErrorCount(): string;

    /**
     * Set Entity
     *
     * @param string $entity
     * @return $this
     */
    public function setEntity($entity);

    /**
     * Set Behavior
     *
     * @param string $behavior
     * @return $this
     */
    public function setBehavior($behavior);

    /**
     * Set Validation Strategy
     *
     * @param string $validationStrategy
     * @return $this
     */
    public function setValidationStrategy($validationStrategy);

    /**
     *  Set Allowed Error Count
     *
     * @param string $allowedErrorCount
     * @return $this
     */
    public function setAllowedErrorCount($allowedErrorCount);
}
