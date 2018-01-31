<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Argument\SearchCriteria;

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
    public function create(string $argumentName)
    {
        $appliers = [
            ArgumentApplier\Filter::ARGUMENT_NAME => ArgumentApplier\Filter::class,
            ArgumentApplier\PageSize::ARGUMENT_NAME => ArgumentApplier\PageSize::class,
            ArgumentApplier\CurrentPage::ARGUMENT_NAME => ArgumentApplier\CurrentPage::class,
            ArgumentApplier\Sort::ARGUMENT_NAME => ArgumentApplier\Sort::class,
            ArgumentApplier\Search::ARGUMENT_NAME => ArgumentApplier\Search::class
        ];
        if (isset($appliers[$argumentName])) {
            return $this->objectManager->create($appliers[$argumentName]);
        } else {
            throw new \LogicException(sprintf('Applier %s not found', $argumentName));
        }
    }
}
