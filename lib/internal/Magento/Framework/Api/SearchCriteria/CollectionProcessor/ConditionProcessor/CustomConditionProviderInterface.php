<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionInterface;

/**
 * Provides collections of custom condition processors (CustomConditionInterface)
 *
 * Used to store processors as mapping attributeName => CustomConditionInterface
 * You can use di.xml to configure with any custom conditions you need
 *
 * @api
 */
interface CustomConditionProviderInterface
{
    /**
     * Get custom processor by field name
     *
     * @param string $fieldName
     * @return CustomConditionInterface
     * @throws InputException
     */
    public function getProcessorByField(string $fieldName): CustomConditionInterface;

    /**
     * Check if collection has custom processor for given field name
     *
     * @param string $fieldName
     * @return bool
     */
    public function hasProcessorForField(string $fieldName): bool;
}
