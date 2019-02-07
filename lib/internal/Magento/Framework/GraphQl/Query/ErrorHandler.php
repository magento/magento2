<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

/**
 * Class ErrorHandler
 *
 * @package Magento\Framework\GraphQl\Query
 */
class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * Handle errors
     *
     * @param \GraphQL\Error\Error[] $errors
     * @param callable               $formatter
     *
     * @return array
     */
    public function handle(array $errors, callable $formatter):array
    {
        return array_map($formatter, $errors);
    }
}
