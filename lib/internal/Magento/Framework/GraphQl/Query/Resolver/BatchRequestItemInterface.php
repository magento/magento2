<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * One of requests for a batch resolver to process.
 *
 * @api
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
