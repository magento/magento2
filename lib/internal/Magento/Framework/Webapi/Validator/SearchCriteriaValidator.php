<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Validator;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Validates search criteria inputs
 */
class SearchCriteriaValidator implements ServiceInputValidatorInterface
{
    /**
     * @var int
     */
    private $maximumPageSize;

    /**
     * @param int $maximumPageSize
     */
    public function __construct(int $maximumPageSize)
    {
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
        if ($entity instanceof SearchCriteriaInterface
            && $propertyName === 'pageSize'
            && $value > $this->maximumPageSize
        ) {
            throw new LocalizedException(
                __('Maximum SearchCriteria pageSize is %max', ['max' => $this->maximumPageSize])
            );
        }
    }
}
