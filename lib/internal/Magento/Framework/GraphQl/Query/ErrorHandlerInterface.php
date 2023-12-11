<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use GraphQL\Error\Error;

/**
 * Interface ErrorHandlerInterface
 *
 * GraphQL error handler
 *
 * @api
 * @see \Magento\Framework\GraphQl\Query\QueryProcessor
 */
interface ErrorHandlerInterface
{
    /**
     * Handle errors
     *
     * @param Error[] $errors
     * @param callable $formatter
     *
     * @return array
     */
    public function handle(array $errors, callable $formatter): array;
}
