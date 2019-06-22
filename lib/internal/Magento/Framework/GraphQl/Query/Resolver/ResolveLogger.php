<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver;

use GraphQL\Error\ClientAware;
use Psr\Log\LoggerInterface;

/**
 * @inheritDoc
 */
class ResolveLogger implements ResolveLoggerInterface
{
    /**
     * @var LoggerInterface
     */
    private $clientLogger;

    /**
     * @var LoggerInterface
     */
    private $serverLogger;

    /**
     * @param LoggerInterface $clientLogger
     * @param LoggerInterface $serverLogger
     */
    public function __construct(
        LoggerInterface $clientLogger,
        LoggerInterface $serverLogger
    ) {
        $this->clientLogger = $clientLogger;
        $this->serverLogger = $serverLogger;
    }

    /**
     * @inheritDoc
     */
    public function execute(ClientAware $clientAware): LoggerInterface
    {
        return $clientAware->isClientSafe() ?
            $this->clientLogger :
            $this->serverLogger;
    }
}
