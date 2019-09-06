<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionInterface;
use Magento\Framework\Phrase;

/**
 * Collection of all custom condition processors
 */
class CustomConditionProvider implements CustomConditionProviderInterface
{
    /**
     * @var CustomConditionInterface[]
     */
    private $customConditionProcessors;

    /**
     * @param array $customConditionProcessors
     * @throws InputException
     */
    public function __construct(array $customConditionProcessors = [])
    {
        foreach ($customConditionProcessors as $processor) {
            if (!$processor instanceof CustomConditionInterface) {
                throw new InputException(
                    new Phrase('Custom processor must implement "%1".', [CustomConditionInterface::class])
                );
            }
        }

        $this->customConditionProcessors = $customConditionProcessors;
    }

    /**
     * Get custom processor by field name
     *
     * @param string $fieldName
     * @return CustomConditionInterface
     * @throws InputException
     */
    public function getProcessorByField(string $fieldName): CustomConditionInterface
    {
        if (!$this->hasProcessorForField($fieldName)) {
            throw new InputException(
                new Phrase('Custom processor for field "%1" is absent.', [$fieldName])
            );
        }

        return $this->customConditionProcessors[$fieldName];
    }

    /**
     * Check if collection has custom processor for given field name
     *
     * @param string $fieldName
     * @return bool
     */
    public function hasProcessorForField(string $fieldName): bool
    {
        return array_key_exists($fieldName, $this->customConditionProcessors);
    }
}
