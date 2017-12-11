<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\GraphQl\Argument\Filter;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for @see FilterArgumentValue class
 */
class FilterArgumentValueFactory
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
     * Create a FilterArgumentValue class
     *
     * @param Connective $connective
     * @return FilterArgumentValue
     */
    public function create(Connective $connective)
    {
        return $this->objectManager->create(
            FilterArgumentValue::class,
            [
                'value' => $connective,
            ]
        );
    }
}
