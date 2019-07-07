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
 * Resolve which logger to use for certain ClientAware exception
 *
 * @api
 */
interface LoggerFactoryInterface
{
    /**
     * Get logger to use for certain ClientAware exception
     *
     * @param ClientAware $clientAware
     *
     * @return LoggerInterface
     */
    public function getLogger(ClientAware $clientAware): LoggerInterface;
}
