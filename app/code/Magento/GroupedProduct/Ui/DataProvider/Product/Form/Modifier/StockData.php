<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProductType;

/**
 * Class StockData hides unnecessary fields in Advanced Inventory Modal
 * @since 2.1.0
 */
class StockData extends AbstractModifier
{
    /**
     * @var LocatorInterface
     * @since 2.1.0
     */
    protected $locator;

    /**
     * @param LocatorInterface $locator
     * @since 2.1.0
     */
    public function __construct(LocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function modifyMeta(array $meta)
    {
        if ($this->locator->getProduct()->getTypeId() === GroupedProductType::TYPE_CODE) {
            $config['arguments']['data']['config'] = [
                'visible' => 0,
                'imports' => [
                    'visible' => null,
                ],
            ];

            $meta['advanced_inventory_modal'] = [
                'children' => [
                    'stock_data' => [
                        'children' => [
                            'qty' => $config,
                            'container_min_qty' => $config,
                            'container_min_sale_qty' => $config,
                            'container_max_sale_qty' => $config,
                            'is_qty_decimal' => $config,
                            'is_decimal_divided' => $config,
                            'container_backorders' => $config,
                            'container_notify_stock_qty' => $config,
                        ],
                    ],
                ],
            ];
        }

        return $meta;
    }
}
