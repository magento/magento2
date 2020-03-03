<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;

/**
 * Request for a resolver.
 */
interface ResolveRequestInterface
{
    /**
     * Field metadata.
     *
     * @return Field
     */
    public function getField(): Field;

    /**
     * GraphQL context.
     *
     * @return ContextInterface
     */
    public function getContext(): ContextInterface;

    /**
     * Information associated with the request.
     *
     * @return ResolveInfo
     */
    public function getInfo(): ResolveInfo;

    /**
     * Value passed from parent resolvers.
     *
     * @return array|null
     */
    public function getValue(): ?array;

    /**
     * Arguments from GraphQL request.
     *
     * @return array|null
     */
    public function getArgs(): ?array;
}
