<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\ObjectManagerInterface;

/**
 * Create instance of QueryModifierInterface
 */
class QueryModifierFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create instance of QueryModifierInterface
     *
     * @param string $queryModifierClassName
     * @param array $data
     * @return QueryModifierInterface
     */
    public function create($queryModifierClassName, $data)
    {
        return $this->objectManager->create($queryModifierClassName, $data);
    }
}
