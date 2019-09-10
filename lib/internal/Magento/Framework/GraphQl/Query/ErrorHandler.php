<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use GraphQL\Error\ClientAware;
use Psr\Log\LoggerInterface;

/**
 * @inheritDoc
 *
 * @package Magento\Framework\GraphQl\Query
 */
class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $errors, callable $formatter): array
    {
        return array_map(
            function (ClientAware $error) use ($formatter) {
                $this->logger->error($error);

                return $formatter($error);
            },
            $errors
        );
    }
}
