<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\ResolverInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * Abstract class for resolving field name.
 */
abstract class Resolver implements ResolverInterface
{
    /**
     * @var ResolverInterface
     */
    private $next;

    /**
     * @param ResolverInterface $resolver
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->next = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public abstract function getFieldName($attributeCode, $context = []): string;

    /**
     * {@inheritdoc}
     */
    public function getNext(): ResolverInterface
    {
        if (!$this->hasNext()) {
            throw new NotFoundException(__('Next resolver not found.'));
        }

        return $this->next;
    }

    /**
     * {@inheritdoc}
     */
    public function hasNext(): bool
    {
        return null !== $this->next;
    }
}
