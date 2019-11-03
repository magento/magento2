<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for the creation of @see ArgumentApplierInterface classes used in search criteria
 */
class ArgumentApplierFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create a search criteria argument applier instance
     *
     * @param string $argumentName
     * @return ArgumentApplierInterface
     * @throws \LogicException
     */
    public function create(string $argumentName) : ArgumentApplierInterface
    {
        $appliers = [
            ArgumentApplier\Filter::ARGUMENT_NAME => ArgumentApplier\Filter::class,
            ArgumentApplier\Sort::ARGUMENT_NAME => ArgumentApplier\Sort::class
        ];
        if (isset($appliers[$argumentName])) {
            return $this->objectManager->create($appliers[$argumentName]);
        } else {
            throw new \LogicException(sprintf('Applier %s not found', $argumentName));
        }
    }
}
