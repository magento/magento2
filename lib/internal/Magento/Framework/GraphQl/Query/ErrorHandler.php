<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use GraphQL\Error\ClientAware;
use Magento\Framework\GraphQl\Query\Resolver\ResolveLoggerInterface;

/**
 * @inheritDoc
 *
 * @package Magento\Framework\GraphQl\Query
 */
class ErrorHandler implements ErrorHandlerInterface
{
    const SERVER_LOG_FILE = 'var/log/graphql/server/exception.log';
    const CLIENT_LOG_FILE = 'var/log/graphql/client/exception.log';

    /**
     * @var ResolveLoggerInterface
     */
    private $resolveLogger;

    /**
     * @param ResolveLoggerInterface $resolveLogger
     */
    public function __construct(
        ResolveLoggerInterface $resolveLogger
    ) {
        $this->resolveLogger = $resolveLogger;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $errors, callable $formatter): array
    {
        return array_map(
            function (ClientAware $error) use ($formatter) {
                $this->resolveLogger->execute($error)->error($error);

                return $formatter($error);
            },
            $errors
        );
    }
}
