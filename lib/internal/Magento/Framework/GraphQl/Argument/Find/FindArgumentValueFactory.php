<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\GraphQl\Argument\Find;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for @see FindArgumentValue class
 */
class FindArgumentValueFactory
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
     * Create a FindArgumentValue class
     *
     * @param Connective $connective
     * @return FindArgumentValue
     */
    public function create(Connective $connective)
    {
        return $this->objectManager->create(
            FindArgumentValue::class,
            [
                'value' => $connective,
            ]
        );
    }
}
