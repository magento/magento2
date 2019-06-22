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
 * @package Magento\Framework\GraphQl\Query
 */
interface ErrorHandlerInterface
{
    const SERVER_LOG_FILE = 'var/log/graphql/server/exception.log';
    const CLIENT_LOG_FILE = 'var/log/graphql/client/exception.log';
    const GENERAL_LOG_FILE = 'var/log/graphql/exception.log';
    /**
     * Handle errors
     *
     * @param Error[] $errors
     * @param callable               $formatter
     *
     * @return array
     */
    public function handle(array $errors, callable $formatter):array;
}
