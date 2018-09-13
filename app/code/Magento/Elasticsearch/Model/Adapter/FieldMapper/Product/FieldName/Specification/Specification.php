<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\Specification;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\SpecificationInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * Abstract class for resolving type of specification.
 */
abstract class Specification implements SpecificationInterface
{
    /**
     * @var SpecificationInterface
     */
    private $next;

    /**
     * @param SpecificationInterface $specification
     */
    public function __construct(SpecificationInterface $specification)
    {
        $this->next = $specification;
    }

    /**
     * {@inheritdoc}
     */
    public abstract function resolve(string $attributeCode): string;

    /**
     * {@inheritdoc}
     */
    public function getNext(): SpecificationInterface
    {
        if (!$this->hasNext()) {
            throw new NotFoundException(__('Next specification not found.'));
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
