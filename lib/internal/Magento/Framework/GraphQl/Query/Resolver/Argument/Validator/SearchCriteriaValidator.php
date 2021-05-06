<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument\Validator;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\ValidatorInterface;

/**
 * Enforces limits on SearchCriteria arguments
 */
class SearchCriteriaValidator implements ValidatorInterface
{
    /**
     * @var int
     */
    private $minPageSize;

    /**
     * @var int
     */
    private $maxPageSize;

    /**
     * @param int $minPageSize
     * @param int $maxPageSize
     */
    public function __construct(int $minPageSize, int $maxPageSize)
    {
        $this->minPageSize = $minPageSize;
        $this->maxPageSize = $maxPageSize;
    }

    /**
     * @inheritDoc
     */
    public function validate(Field $field, $args): void
    {
        if (isset($args['pageSize'])) {
            if ($args['pageSize'] < $this->minPageSize) {
                throw new GraphQlInputException(
                    __("Minimum pageSize is %min", ['min' => $this->minPageSize])
                );
            }
            if ($args['pageSize'] > $this->maxPageSize) {
                throw new GraphQlInputException(
                    __("Maximum pageSize is %max", ['max' => $this->maxPageSize])
                );
            }
        }
    }
}
