<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Method\Specification;

use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Model\Method\SpecificationInterface;

/**
 * Specification Factory
 */
class Factory
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
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
