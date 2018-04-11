<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Ui\Component\Control\SourceSelection;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Ui\Component\Control\Container;
use Magento\InventorySourceSelectionApi\Api\GetSourceSelectionAlgorithmListInterface;
use Magento\InventoryShipping\Model\SourceSelection\GetDefaultSourceSelectionAlgorithmCodeInterface;

class AlgorithmSelectionButton implements ButtonProviderInterface
{
    /**
     * @var string
     */
    private $targetName;

    /**
     * @var GetSourceSelectionAlgorithmListInterface
     */
    private $getSourceSelectionAlgorithmList;

    /**
     * @var GetDefaultSourceSelectionAlgorithmCodeInterface
     */
    private $getDefaultSourceSelectionAlgorithmCode;

    /**
     * @param GetSourceSelectionAlgorithmListInterface $getSourceSelectionAlgorithmList
     * @param GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
     * @param string $targetName
     */
    public function __construct(
        string $targetName,
        GetSourceSelectionAlgorithmListInterface $getSourceSelectionAlgorithmList,
        GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
    ) {
        $this->targetName = $targetName;
        $this->getSourceSelectionAlgorithmList = $getSourceSelectionAlgorithmList;
        $this->getDefaultSourceSelectionAlgorithmCode = $getDefaultSourceSelectionAlgorithmCode;
    }

    /**
     * Retrieve button-specified settings
     *
     * @return array
     */
    public function getButtonData()
    {
        $defaultSAlgorithmCode = $this->getDefaultSourceSelectionAlgorithmCode->execute();
        return [
            'label' => __('Source Selection Algorithm'),
            'class' => 'save ',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => $this->targetName,
                                'actionName' => 'processAlgorithm',
                                'params' => [
                                    false,
                                    [
                                        'algorithm_code' => $defaultSAlgorithmCode,
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'class_name' => Container::SPLIT_BUTTON,
            'options' => $this->getAlgorithmsListOptions(),
            'sort_order' => 10,
        ];
    }

    /**
     * Retrieve options for 'AlgorithmActionList' split button
     *
     * @return array
     */
    protected function getAlgorithmsListOptions()
    {
        $algorithmsList = $this->getSourceSelectionAlgorithmList->execute();
        $splitButtonOptions = [];
        foreach ($algorithmsList as $algorithm) {
            $splitButtonOptions[] =  [
                'label' => $algorithm->getTitle(),
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => $this->targetName,
                                    'actionName' => 'processAlgorithm',
                                    'params' => [
                                        false,
                                        [
                                            'algorithm_code' => $algorithm->getCode(),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }

        return $splitButtonOptions;
    }
}
