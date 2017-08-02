<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

/**
 * Disable Quantity field by default
 * @since 2.1.0
 */
class BundleQuantity extends AbstractModifier
{
    const CODE_QUANTITY_AND_STOCK_STATUS = 'quantity_and_stock_status';
    const CODE_QUANTITY = 'qty';
    const CODE_QTY_CONTAINER = 'quantity_and_stock_status_qty';

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function modifyMeta(array $meta)
    {
        if ($groupCode = $this->getGroupCodeByField($meta, 'container_' . self::CODE_QUANTITY_AND_STOCK_STATUS)) {
            $parentChildren = &$meta[$groupCode]['children'];
            if (!empty($parentChildren['container_' . self::CODE_QUANTITY_AND_STOCK_STATUS])) {
                $parentChildren['container_' . self::CODE_QUANTITY_AND_STOCK_STATUS] = array_replace_recursive(
                    $parentChildren['container_' . self::CODE_QUANTITY_AND_STOCK_STATUS],
                    [
                        'children' => [
                            self::CODE_QUANTITY_AND_STOCK_STATUS => [
                                'arguments' => [
                                    'data' => [
                                        'config' => ['disabled' => false],
                                    ],
                                ],
                            ],
                        ]
                    ]
                );
            }
        }

        if ($groupCode = $this->getGroupCodeByField($meta, self::CODE_QTY_CONTAINER)) {
            $parentChildren = &$meta[$groupCode]['children'];
            if (!empty($parentChildren[self::CODE_QTY_CONTAINER])) {
                $parentChildren[self::CODE_QTY_CONTAINER] = array_replace_recursive(
                    $parentChildren[self::CODE_QTY_CONTAINER],
                    [
                        'children' => [
                            self::CODE_QUANTITY => [
                                'arguments' => [
                                    'data' => [
                                        'config' => ['disabled' => true],
                                    ],
                                ],
                            ],
                        ],
                    ]
                );
            }
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
