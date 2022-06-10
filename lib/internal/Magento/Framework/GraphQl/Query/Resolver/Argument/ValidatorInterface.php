<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Config\Element\Field;

/**
 * Validation support for resolver arguments
 */
interface ValidatorInterface
{
    /**
     * Validate resolver args
     *
     * @param Field $field
     * @param mixed $args
     * @throws GraphQlInputException
     */
    public function validate(Field $field, $args): void;
}
