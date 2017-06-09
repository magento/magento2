<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Ui\Component\Control;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Ui\Component\Control\Container;

/**
 * @api
 */
class SaveSplitButton implements ButtonProviderInterface
{
    /**
     * @var string
     */
    private $saveTarget;

    /**
     * @param string $saveTarget
     */
    public function __construct($saveTarget)
    {
        $this->saveTarget = $saveTarget;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save &amp; Continue'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => $this->saveTarget,
                                'actionName' => 'save',
                                'params' => [
                                    // first param is redirect flag
                                    false,
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'class_name' => Container::SPLIT_BUTTON,
            'options' => $this->getOptions(),
            'sort_order' => 40,
        ];
    }

    /**
     * @return array
     */
    private function getOptions()
    {
        $options = [
            [
                'label' => __('Save &amp; Close'),
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => $this->saveTarget,
                                    'actionName' => 'save',
                                    'params' => [
                                        // first param is redirect flag
                                        true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'sort_order' => 10,
            ],
            [
                'label' => __('Save &amp; New'),
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => $this->saveTarget,
                                    'actionName' => 'save',
                                    'params' => [
                                        // first param is redirect flag, second is data that will be added to post
                                        // request
                                        true,
                                        [
                                            'redirect_to_new' => 1,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'sort_order' => 20,
            ],
        ];
        return $options;
    }
}