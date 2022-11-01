<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Sales\Order\Pdf\Items;

/**
 * Data provider class for ShipmentTest class
 */
class ShipmentTestProvider
{
    /**
     * Returns shipment test variations data
     *
     * @return array[]
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getData(): array
    {
        return
            [
                [
                    [
                        1 =>
                            [
                                'lines' =>
                                    [
                                        0 =>
                                            [
                                                0 =>
                                                    [
                                                        'text' => 0,
                                                        'feed' => 35,
                                                    ],
                                                1 =>
                                                    [
                                                        'text' =>
                                                            [
                                                                0 => 'Bundle',
                                                            ],
                                                        'feed' => 100,
                                                    ],
                                                2 =>
                                                    [
                                                        'text' =>
                                                            [
                                                                0 => 'bundle-simple',
                                                            ],
                                                        'feed' => 565,
                                                        'align' => 'right',
                                                    ],
                                            ],
                                        1 =>
                                            [
                                                0 =>
                                                    [
                                                        'text' => 0,
                                                        'feed' => 35,
                                                    ],
                                                1 =>
                                                    [
                                                        'text' =>
                                                            [
                                                                0 => 'Simple1',
                                                            ],
                                                        'feed' => 100,
                                                    ],
                                                2 =>
                                                    [
                                                        'text' =>
                                                            [
                                                                0 => 'simple1',
                                                            ],
                                                        'feed' => 565,
                                                        'align' => 'right',
                                                    ],
                                            ],
                                    ],
                                'height' => 15,
                            ],
                        0 =>
                            [
                                'lines' =>
                                    [
                                        0 =>
                                            [
                                                0 =>
                                                    [
                                                        'text' => 0,
                                                        'feed' => 35,
                                                    ],
                                                1 =>
                                                    [
                                                        'text' =>
                                                            [
                                                                0 => 'Simple2',
                                                            ],
                                                        'feed' => 100,
                                                    ],
                                                2 =>
                                                    [
                                                        'text' =>
                                                            [
                                                                0 => 'simple2',
                                                            ],
                                                        'feed' => 565,
                                                        'align' => 'right',
                                                    ],
                                            ],
                                    ],
                                'height' => 15,
                            ],
                    ]
                ]
            ];
    }
}
