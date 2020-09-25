<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\View;

use Magento\Framework\ObjectManagerInterface;

class ChangeLogBatchWalkerFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * ChangeLogBatchWalkerFactory constructor.
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
     * @param array $data
     * @return ChangeLogBatchWalkerInterface
     */
    public function create(string $batchWalkerClassName, array $data): ChangeLogBatchWalkerInterface
    {
        return $this->objectManager->create($batchWalkerClassName, $data);
    }
}
