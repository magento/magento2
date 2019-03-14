<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\InventorySourceSelectionApi\Model\SourceSelectionInterface;

class SourceSelectionService implements SourceSelectionServiceInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $sourceSelectionMethods;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $sourceSelectionMethods
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $sourceSelectionMethods = []
    ) {
        $this->objectManager = $objectManager;
        $this->sourceSelectionMethods = $sourceSelectionMethods;
    }

    /**
     * @inheritdoc
     */
    public function execute(
        InventoryRequestInterface $inventoryRequest,
        string $algorithmCode
    ): SourceSelectionResultInterface {
        if (!isset($this->sourceSelectionMethods[$algorithmCode])) {
            throw new \LogicException(
                __('There is no such Source Selection Algorithm implemented: %1', $algorithmCode)
            );
        }
        $sourceSelectionClassName = $this->sourceSelectionMethods[$algorithmCode];

        $sourceSelectionAlgorithm = $this->objectManager->create($sourceSelectionClassName);
        if (false === $sourceSelectionAlgorithm instanceof SourceSelectionInterface) {
            throw new \LogicException(
                __('%1 doesn\'t implement SourceSelectionInterface', $sourceSelectionClassName)
            );
        }
        return $sourceSelectionAlgorithm->execute($inventoryRequest);
    }
}
