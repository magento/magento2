<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Method\Specification;

use Magento\Payment\Model\Method\SpecificationInterface;

/**
 * Composite specification
 *
 * Use this class for virtual types declaration.
 *
 * @api
 * @since 2.0.0
 */
class Composite implements SpecificationInterface
{
    /**
     * Specifications collection
     *
     * @var SpecificationInterface[]
     * @since 2.0.0
     */
    protected $specifications = [];

    /**
     * Construct
     *
     * @param Factory $factory
     * @param array $specifications
     * @since 2.0.0
     */
    public function __construct(Factory $factory, $specifications = [])
    {
        foreach ($specifications as $specification) {
            $this->specifications[] = $factory->create($specification);
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isSatisfiedBy($paymentMethod)
    {
        foreach ($this->specifications as $specification) {
            if (!$specification->isSatisfiedBy($paymentMethod)) {
                return false;
            }
        }
        return true;
    }
}
