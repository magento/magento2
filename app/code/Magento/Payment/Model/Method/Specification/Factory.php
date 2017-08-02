<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Method\Specification;

use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Model\Method\SpecificationInterface;

/**
 * Specification Factory
 * @since 2.0.0
 */
class Factory
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create specification instance
     *
     * @param string $specificationClass
     * @return SpecificationInterface
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function create($specificationClass)
    {
        $specification = $this->objectManager->get($specificationClass);
        if (!$specification instanceof SpecificationInterface) {
            throw new \InvalidArgumentException('Specification must implement SpecificationInterface');
        }
        return $specification;
    }
}
