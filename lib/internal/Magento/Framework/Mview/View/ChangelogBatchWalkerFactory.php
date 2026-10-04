<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Mview\View;

use Magento\Framework\ObjectManagerInterface;

class ChangelogBatchWalkerFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * ChangelogBatchWalkerFactory constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Instantiate BatchWalker interface
     *
     * @param string $batchWalkerClassName
     * @return ChangelogBatchWalkerInterface
     */
    public function create(string $batchWalkerClassName): ChangelogBatchWalkerInterface
    {
        return $this->objectManager->create($batchWalkerClassName);
    }
}
