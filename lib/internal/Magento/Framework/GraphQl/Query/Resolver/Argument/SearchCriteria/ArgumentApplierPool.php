<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria;

use Magento\Framework\ObjectManagerInterface;

/**
 * Pool class for the retrieval of @see ArgumentApplierInterface classes
 */
class ArgumentApplierPool
{
    /** @var ArgumentApplierInterface[]  */
    private $appliers = [];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param $appliers
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $appliers = [
            ArgumentApplier\Filter::ARGUMENT_NAME => ArgumentApplier\Filter::class,
            ArgumentApplier\Sort::ARGUMENT_NAME => ArgumentApplier\Sort::class
        ]
    ) {
        $this->objectManager = $objectManager;
        $this->appliers = $appliers;
    }

    /**
     * Create a search criteria argument applier instance
     *
     * @param string $argumentName
     * @return ArgumentApplierInterface
     * @throws \LogicException
     */
    public function getApplier(string $argumentName) : ArgumentApplierInterface
    {
        if (isset($this->appliers[$argumentName])) {
            return $this->objectManager->get($this->appliers[$argumentName]);
        } else {
            throw new \LogicException(sprintf('Applier %s not found', $argumentName));
        }
    }

    /**
     * Check to see if a argument applier instance is configured
     *
     * @param string $argumentName
     * @return bool
     */
    public function hasApplier(string $argumentName) : bool
    {
        if (isset($this->appliers[$argumentName])) {
            return true;
        } else {
            return false;
        }
    }
}
