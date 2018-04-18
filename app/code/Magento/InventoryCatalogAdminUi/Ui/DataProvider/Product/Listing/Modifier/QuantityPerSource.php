<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Ui\DataProvider\Product\Listing\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;
use Magento\InventoryCatalogAdminUi\Ui\Component\Listing\Column\SourceItems;

/**
 * Quantity Per Source modifier on CatalogInventory Product Grid
 */
class QuantityPerSource extends AbstractModifier
{
    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var SourceItems
     */
    private $sourceItems;

    /**
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param SourceItems $sourceItems
     */
    public function __construct(
        IsSingleSourceModeInterface $isSingleSourceMode,
        SourceItems $sourceItems
    ) {
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->sourceItems = $sourceItems;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $this->sourceItems->prepareDataSource(['data' => $data])['data'];
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        if (true === $this->isSingleSourceMode->execute()) {
            return $meta;
        }

        return array_merge_recursive($meta, [
            'product_columns' => [
                'children' => [
                    'quantity_per_source' => [
                        'arguments' => $this->getArguments(),
                        'attributes' => $this->getAttributes(),
                        'children' => [],
                    ]
                ]
            ]
        ]);
    }

    /**
     * @return array
     */
    private function getArguments(): array
    {
        return [
            'data' => [
                'config' => [
                    'dataType' => 'text',
                    'component' => 'Magento_InventoryCatalogAdminUi/js/product/grid/cell/source-items',
                    'componentType' => 'column',
                    'sortable' => false,
                    'filter' => false,
                    'label' => __('Quantity per Source'),
                    'sortOrder' => 76,
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function getAttributes(): array
    {
        return [
            'class' => SourceItems::class,
            'component' => 'Magento_InventoryCatalogAdminUi/js/product/grid/cell/source-items',
            'name' => 'quantity_per_source',
            'sortOrder' => 76
        ];
    }
}
