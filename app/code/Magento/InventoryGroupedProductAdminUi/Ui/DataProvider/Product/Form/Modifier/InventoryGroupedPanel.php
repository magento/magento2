<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryGroupedProductAdminUi\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProductType;
use Magento\GroupedProduct\Ui\DataProvider\Product\Form\Modifier\Grouped;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryGroupedProductAdminUi\Model\GetQuantityInformationPerSourceBySkus;
use Magento\Ui\Component\Form\Element\Input;

/**
 * Add column "Quantity Per Source" and sources data to grouped product assigned products grid.
 */
class InventoryGroupedPanel extends AbstractModifier
{
    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var GetQuantityInformationPerSourceBySkus
     */
    private $getQuantityInformationPerSourceBySkus;

    /**
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param LocatorInterface $locator
     * @param GetQuantityInformationPerSourceBySkus $getQuantityInformationPerSourceBySkus
     */
    public function __construct(
        IsSingleSourceModeInterface $isSingleSourceMode,
        LocatorInterface $locator,
        GetQuantityInformationPerSourceBySkus $getQuantityInformationPerSourceBySkus
    ) {
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->locator = $locator;
        $this->getQuantityInformationPerSourceBySkus = $getQuantityInformationPerSourceBySkus;
    }

    /**
     * Add source data to linked to grouped product items only for multi source mode.
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();
        $modelId = $product->getId();

        if ($product->getTypeId() === GroupedProductType::TYPE_CODE
            && $modelId
            && isset($data[$modelId]['links'][Grouped::LINK_TYPE])
            && !$this->isSingleSourceMode->execute()
        ) {
            $linkSkus = [];

            foreach ($data[$modelId]['links'][Grouped::LINK_TYPE] as $linkData) {
                $linkSkus[] = $linkData['sku'];
            }

            $sourceItemsData = $this->getQuantityInformationPerSourceBySkus->execute($linkSkus);

            foreach ($data[$modelId]['links'][Grouped::LINK_TYPE] as &$productLinkData) {
                $productLinkData['quantity_per_source'] = $sourceItemsData[$productLinkData['sku']] ?? [];
            }
        }

        return $data;
    }

    /**
     * Add column "Quantity Per Source" to assigned grouped products grid for multi source mode.
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        if ($this->locator->getProduct()->getTypeId() === GroupedProductType::TYPE_CODE
            && !$this->isSingleSourceMode->execute()
        ) {
            $meta = array_replace_recursive($meta, [
                'grouped' => [
                    'children' => [
                        'associated' => [
                            'children' => [
                                'record' => [
                                    'children' => [
                                        'source_code' => [
                                            'arguments' => [
                                                'data' => [
                                                    'config' => $this->getQuantityPerSourceConfig()
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'map' => [
                                            'quantity_per_source' => 'quantity_per_source'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
        }

        return $meta;
    }

    /**
     * Config for field "Quantity Per Source" on dynamic rows.
     *
     * @return array
     */
    private function getQuantityPerSourceConfig(): array
    {
        return [
            'componentType' => 'text',
            'component' => 'Magento_InventoryGroupedProductAdminUi/js/form/element/quantity-per-source',
            'template' => 'ui/form/field',
            'dataScope' => 'quantity_per_source',
            'label' => __('Quantity Per Source'),
            'formElement' => Input::NAME,
        ];
    }
}
