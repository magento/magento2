<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor;

use Magento\Framework\Exception\InputException;

/**
 * Interface CustomConditionProcessorBuilderInterface
 * Interface to build collections of all custom condition processors
 *
 * @package Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor
 */
interface CustomConditionProcessorBuilderInterface
{
    /**
     * Get custom processor by field name
     *
     * @param $fieldName
     * @return \Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionInterface
     * @throws InputException
     */
    public function getProcessorByField($fieldName);

    /**
     * Check if collection has custom processor for given field name
     *
     * @param $fieldName
     * @return bool
     */
    public function hasProcessorForField($fieldName);
}
