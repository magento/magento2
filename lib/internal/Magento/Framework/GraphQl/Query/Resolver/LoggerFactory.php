<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver;

use GraphQL\Error\ClientAware;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritDoc
 */
class LoggerFactory implements LoggerFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritDoc
     */
    public function getLogger(ClientAware $clientAware): LoggerInterface
    {
        return $clientAware->isClientSafe() ?
            $this->objectManager->get('GraphQLClientLogger') :
            $this->objectManager->get('GraphQLServerLogger');
    }
}
