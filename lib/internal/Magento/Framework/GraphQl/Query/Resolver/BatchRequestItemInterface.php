<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * One of requests for a batch resolver to process.
 */
interface BatchRequestItemInterface
{
    /**
     * Meta for current branch/leaf.
     *
     * @return ResolveInfo
     */
    public function getInfo(): ResolveInfo;

    /**
     * Values passed from parent resolvers.
     *
     * @return array|null
     */
    public function getValue(): ?array;

    /**
     * GraphQL request arguments.
     *
     * @return array|null
     */
    public function getArgs(): ?array;
}
