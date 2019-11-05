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
 * Request made to a resolver.
 */
class ResolveRequest implements BatchRequestItemInterface, ResolveRequestInterface
{
    /**
     * @var Field
     */
    private $field;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var ResolveInfo
     */
    private $info;

    /**
     * @var array|null
     */
    private $value;

    /**
     * @var array|null
     */
    private $args;

    /**
     * ResolverRequest constructor.
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     */
    public function __construct(
        Field $field,
        ContextInterface $context,
        ResolveInfo $info,
        ?array $value,
        ?array $args
    ) {
        $this->field = $field;
        $this->context = $context;
        $this->info = $info;
        $this->value = $value;
        $this->args = $args;
    }

    /**
     * @inheritDoc
     */
    public function getField(): Field
    {
        return $this->field;
    }

    /**
     * @inheritDoc
     */
    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    /**
     * @inheritDoc
     */
    public function getInfo(): ResolveInfo
    {
        return $this->info;
    }

    /**
     * @inheritDoc
     */
    public function getValue(): ?array
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function getArgs(): ?array
    {
        return $this->args;
    }
}
