<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\GraphQl\Argument\Find;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for FindArgumentValue class
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
     * @param Clause|Connective $clause
     * @return FindArgumentValue
     */
    public function create($clause)
    {
        return $this->objectManager->create(
            FindArgumentValue::class,
            [
                'clause' => $clause,
            ]
        );
    }
}
