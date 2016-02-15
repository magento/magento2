<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Source\Stock;
use Magento\Catalog\Ui\DataProvider\Grouper;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Data provider for advanced inventory form
 */
class AdvancedInventory extends AbstractModifier
{
    const STOCK_DATA_FIELDS = 'stock_data';

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var Grouper
     */
    protected $grouper;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var Stock
     */
    private $stock;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @param LocatorInterface $locator
     * @param Grouper $grouper
     * @param Stock $stock
     * @param StockRegistryInterface $stockRegistry
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        Grouper $grouper,
        Stock $stock,
        StockRegistryInterface $stockRegistry,
        ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->grouper = $grouper;
        $this->stockRegistry = $stockRegistry;
        $this->stock = $stock;
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $fieldCode = 'quantity_and_stock_status';

        $model = $this->locator->getProduct();
        $modelId = $model->getId();

        $stockItem = $this->stockRegistry->getStockItem(
            $modelId,
            $model->getStore()->getWebsiteId()
        );

        $stockData = $stockItem->getData();
        if (!empty($stockData)) {
            $data[$modelId][self::DATA_SOURCE_DEFAULT][self::STOCK_DATA_FIELDS] = $stockData;
        }
        if (isset($stockData['is_in_stock'])) {
            $data[$modelId][self::DATA_SOURCE_DEFAULT][$fieldCode]['is_in_stock'] =
                (int)$stockData['is_in_stock'];
        }

        return $this->prepareStockData($data);
    }

    /**
     * Prepare data for stock_data fields
     *
     * @param array $data
     * @return array
     */
    protected function prepareStockData(array $data)
    {
        $productId = $this->locator->getProduct()->getId();
        $stockDataFields = [
            'qty_increments',
            'min_qty',
            'min_sale_qty',
            'max_sale_qty',
            'notify_stock_qty',
        ];

        foreach ($stockDataFields as $field) {
            if (isset($data[$productId][self::DATA_SOURCE_DEFAULT][self::STOCK_DATA_FIELDS][$field])) {
                $data[$productId][self::DATA_SOURCE_DEFAULT][self::STOCK_DATA_FIELDS][$field] =
                    (float)$data[$productId][self::DATA_SOURCE_DEFAULT][self::STOCK_DATA_FIELDS][$field];
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->prepareMeta();

        return $this->meta;
    }

    /**
     * @return void
     */
    private function prepareMeta()
    {
        $fieldCode = 'quantity_and_stock_status';
        $pathField = $this->getElementArrayPath($this->meta, $fieldCode);

        if ($pathField) {
            $labelField = $this->arrayManager->get(
                $this->arrayManager->slicePath($pathField, 0, -2) . '/arguments/data/config/label',
                $this->meta
            );
            $fieldsetPath = $this->arrayManager->slicePath($pathField, 0, -4);

            $this->meta = $this->arrayManager->merge(
                $pathField . '/arguments/data/config',
                $this->meta,
                [
                    'label' => __('Stock Status'),
                    'value' => '1',
                    'dataScope' => $fieldCode . '.is_in_stock',
                    'scopeLabel' => '[GLOBAL]',
                ]
            );
            $this->meta = $this->arrayManager->merge(
                $this->arrayManager->slicePath($pathField, 0, -2) . '/arguments/data/config',
                $this->meta,
                [
                    'label' => __('Stock Status'),
                    'scopeLabel' => '[GLOBAL]',
                ]
            );

            $container['arguments']['data']['config'] = [
                'formElement' => 'container',
                'componentType' => 'container',
                'component' => "Magento_Ui/js/form/components/group",
                'label' => $labelField,
                'breakLine' => false,
                'dataScope' => $fieldCode,
                'scopeLabel' => '[GLOBAL]',
                'source' => 'product_details',
                'sortOrder' =>
                    (int) $this->arrayManager->get(
                        $this->arrayManager->slicePath($pathField, 0, -2) . '/arguments/data/config/sortOrder',
                        $this->meta
                    ) - 1,
            ];
            $qty['arguments']['data']['config'] = [
                'component' => 'Magento_CatalogInventory/js/components/qty-validator-changer',
                'dataType' => 'number',
                'formElement' => 'input',
                'componentType' => 'field',
                'visible' => '1',
                'require' => '0',
                'additionalClasses' => 'admin__field-small',
                'dataScope' => 'qty',
                'validation' => [
                    'validate-number' => true,
                    'validate-digits' => true,
                ],
                'imports' => [
                    'handleChanges' => '${$.provider}:data.product.stock_data.is_qty_decimal',
                ],
                'sortOrder' => 10,
            ];
            $advancedInventoryButton['arguments']['data']['config'] = [
                'displayAsLink' => true,
                'formElement' => 'container',
                'componentType' => 'container',
                'component' => 'Magento_Ui/js/form/components/button',
                'template' => 'ui/form/components/button/container',
                'actions' => [
                    [
                        'targetName' => 'product_form.product_form.advanced_inventory_modal',
                        'actionName' => 'toggleModal',
                    ],
                ],
                'title' => __('Advanced Inventory'),
                'provider' => false,
                'additionalForGroup' => true,
                'source' => 'product_details',
                'sortOrder' => 20,
            ];
            $container['children'] = [
                'qty' => $qty,
                'advanced_inventory_button' => $advancedInventoryButton,
            ];

            $this->meta = $this->arrayManager->merge(
                $fieldsetPath . '/children',
                $this->meta,
                ['quantity_and_stock_status_qty' => $container]
            );
        }
    }
}
