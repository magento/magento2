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
    private $maxPageSize;

    /**
     * @param int $maxPageSize
     */
    public function __construct(int $maxPageSize)
    {
        $this->maxPageSize = $maxPageSize;
    }

    /**
     * @inheritDoc
     */
    public function validate(Field $field, $args): void
    {
        if (isset($args['pageSize']) && $args['pageSize'] > $this->maxPageSize) {
            throw new GraphQlInputException(
                __("Maximum pageSize is %max", ['max' => $this->maxPageSize])
            );
        }
    }
}
