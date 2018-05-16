<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Model;

use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionAlgorithmInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionAlgorithmInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\GetSourceSelectionAlgorithmListInterface;

/**
 * {@inheritdoc}
 *
 * @api
 */
class GetSourceSelectionAlgorithmList implements GetSourceSelectionAlgorithmListInterface
{
    /**
     * @var SourceSelectionAlgorithmInterface[]
     */
    private $availableAlgorithms;

    /**
     * @var SourceSelectionAlgorithmInterfaceFactory
     */
    private $sourceSelectionAlgorithmFactory;

    /**
     * SourceSelectionAlgorithmProvider constructor.
     * @param SourceSelectionAlgorithmInterfaceFactory $sourceSelectionAlgorithmFactory
     * @param array $availableAlgorithms
     */
    public function __construct(
        SourceSelectionAlgorithmInterfaceFactory $sourceSelectionAlgorithmFactory,
        array $availableAlgorithms = []
    ) {
        $this->availableAlgorithms = $availableAlgorithms;
        $this->sourceSelectionAlgorithmFactory = $sourceSelectionAlgorithmFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(): array
    {
        $algorithmsList = [];
        foreach ($this->availableAlgorithms as $data) {
            $algorithmsList[] = $this->sourceSelectionAlgorithmFactory->create([
                'code' => $data['code'],
                'title' => $data['title'],
                'description' => $data['description']
            ]);
        }

        return $algorithmsList;
    }
}
