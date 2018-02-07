<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts\Price;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts\Stock;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Ui\Component\Form\Fieldset;

class Alerts extends AbstractModifier
{
    const DATA_SCOPE       = 'data';
    const DATA_SCOPE_STOCK = 'stock';
    const DATA_SCOPE_PRICE = 'price';

    /**
     * @var string
     */
    private static $previousGroup = 'related';

    /**
     * @var int
     */
    private static $sortOrder = 110;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * Alerts constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LayoutFactory $layoutFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function modifyMeta(array $meta)
    {
        if (!$this->canShowTab()) {
            return $meta;
        }

        $meta = array_replace_recursive(
            $meta,
            [
                'alerts' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'additionalClasses' => 'admin__fieldset-section',
                                'label' => __('Product Alerts'),
                                'collapsible' => true,
                                'componentType' => Fieldset::NAME,
                                'dataScope' => static::DATA_SCOPE,
                                'sortOrder' =>
                                    $this->getNextGroupSortOrder(
                                        $meta,
                                        self::$previousGroup,
                                        self::$sortOrder
                                    ),
                            ],
                        ],
                    ],
                    'children' => [
                        static::DATA_SCOPE_STOCK => $this->getAlertStockFieldset(),
                        static::DATA_SCOPE_PRICE => $this->getAlertPriceFieldset()
                    ],
                ],
            ]
        );

        return $meta;
    }

    /**
     * @return bool
     */
    private function canShowTab()
    {
        $alertPriceAllow = $this->scopeConfig->getValue(
            'catalog/productalert/allow_price',
            ScopeInterface::SCOPE_STORE
        );
        $alertStockAllow = $this->scopeConfig->getValue(
            'catalog/productalert/allow_stock',
            ScopeInterface::SCOPE_STORE
        );

        return ($alertPriceAllow || $alertStockAllow);
    }

    /**
     * Prepares config for the alert stock products fieldset
     * @return array
     */
    private function getAlertStockFieldset()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Alert stock'),
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/html',
                        'additionalClasses' => 'admin__fieldset-note',
                        'content' =>
                            '<h4>' . __('Alert Stock') . '</h4>' .
                            $this->layoutFactory->create()->createBlock(
                                Stock::class
                            )->toHtml(),
                    ]
                ]
            ]
        ];
    }

    /**
     * Prepares config for the alert price products fieldset
     * @return array
     */
    private function getAlertPriceFieldset()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Alert price'),
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/html',
                        'additionalClasses' => 'admin__fieldset-note',
                        'content' =>
                            '<h4>' . __('Alert Price') . '</h4>' .
                            $this->layoutFactory->create()->createBlock(
                                Price::class
                            )->toHtml(),
                    ]
                ]
            ]
        ];
    }
}
