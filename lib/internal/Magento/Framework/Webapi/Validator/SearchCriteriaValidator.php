<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Validator;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InvalidArgumentException;

/**
 * Validates search criteria inputs
 */
class SearchCriteriaValidator implements ServiceInputValidatorInterface
{
    /**
     * @var int
     */
    private $minimumPageSize;

    /**
     * @var int
     */
    private $maximumPageSize;

    /**
     * @param int $minimumPageSize
     * @param int $maximumPageSize
     */
    public function __construct(int $minimumPageSize, int $maximumPageSize)
    {
        $this->minimumPageSize = $minimumPageSize;
        $this->maximumPageSize = $maximumPageSize;
    }

    /**
     * @inheritDoc
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     */
    public function validateComplexArrayType(string $className, array $items): void
    {
    }

    /**
     * @inheritDoc
     */
    public function validateEntityValue(object $entity, string $propertyName, $value): void
    {
        if ($entity instanceof SearchCriteriaInterface && $propertyName === 'pageSize') {
            if ($value < $this->minimumPageSize) {
                throw new InvalidArgumentException(
                    __('Minimum SearchCriteria pageSize is %min', ['min' => $this->minimumPageSize])
                );
            }
            if ($value > $this->maximumPageSize) {
                throw new InvalidArgumentException(
                    __('Maximum SearchCriteria pageSize is %max', ['max' => $this->maximumPageSize])
                );
            }
        }
    }
}
