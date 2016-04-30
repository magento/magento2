<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Unit\Helper;

/**
 * Class SelectRendererTrait
 */
trait SelectRendererTrait
{
    /**
     * @param \Magento\Framework\TestFramework\Unit\Helper\ObjectManager $objectManager
     * @return \Magento\Framework\DB\Select\SelectRenderer
     */
    protected function getSelectRenderer(\Magento\Framework\TestFramework\Unit\Helper\ObjectManager $objectManager)
    {
        return $objectManager->getObject(
            'Magento\Framework\DB\Select\SelectRenderer',
            [
                'renderers' => [
                    'distinct' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\DistinctRenderer'
                        ),
                        'sort' => 11,
                        'part' => 'distinct',
                    ],
                    'columns' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\ColumnsRenderer',
                            [
                                'quote' => $objectManager->getObject('Magento\Framework\DB\Platform\Quote')
                            ]
                        ),
                        'sort' => 11,
                        'part' => 'columns',
                    ],
                    'union' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\UnionRenderer'
                        ),
                        'sort' => 11,
                        'part' => 'union',
                    ],
                    'from' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\FromRenderer',
                            [
                                'quote' => $objectManager->getObject('Magento\Framework\DB\Platform\Quote')
                            ]
                        ),
                        'sort' => 11,
                        'part' => 'from',
                    ],
                    'where' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\WhereRenderer'
                        ),
                        'sort' => 11,
                        'part' => 'where',
                    ],
                    'group' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\GroupRenderer'
                        ),
                        'sort' => 11,
                        'part' => 'group',
                    ],
                    'having' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\HavingRenderer'
                        ),
                        'sort' => 11,
                        'part' => 'having',
                    ],
                    'order' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\OrderRenderer'
                        ),
                        'sort' => 11,
                        'part' => 'order',
                    ],
                    'limit' => [
                        'renderer' => $objectManager->getObject(
                            'Magento\Framework\DB\Select\LimitRenderer'
                        ),
                        'sort' => 11,
                        'part' => 'limitcount',
                    ],
                ],
            ]
        );
    }
}
