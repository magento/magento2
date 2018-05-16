<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Ui\DataProvider\Product\Listing\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Listing\Columns\Column;

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
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemsBySku;

    /**
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param SourceRepositoryInterface $sourceRepository
     * @param GetSourceItemsBySkuInterface $getSourceItemsBySku
     */
    public function __construct(
        IsSingleSourceModeInterface $isSingleSourceMode,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        SourceRepositoryInterface $sourceRepository,
        GetSourceItemsBySkuInterface $getSourceItemsBySku
    ) {
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->sourceRepository = $sourceRepository;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        if (0 === $data['totalRecords'] || true === $this->isSingleSourceMode->execute()) {
            return $data;
        }

        foreach ($data['items'] as &$item) {
            $item['quantity_per_source'] = $this->isSourceItemManagementAllowedForProductType->execute(
                $item['type_id']
            ) === true ? $this->getSourceItemsData($item['sku']) : [];
        }
        unset($item);

        return $data;
    }

    /**
     * @param string $sku
     * @return array
     * @throws NoSuchEntityException
     */
    private function getSourceItemsData(string $sku): array
    {
        $sourceItems = $this->getSourceItemsBySku->execute($sku);

        $sourceItemsData = [];
        foreach ($sourceItems as $sourceItem) {
            $source = $this->sourceRepository->get($sourceItem->getSourceCode());
            $qty = (float)$sourceItem->getQuantity();

            $sourceItemsData[] = [
                'source_name' => $source->getName(),
                'qty' => $qty,
            ];
        }
        return $sourceItemsData;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        if (true === $this->isSingleSourceMode->execute()) {
            return $meta;
        }

        $meta = array_replace_recursive($meta, [
            'product_columns' => [
                'children' => [
                    'quantity_per_source' => $this->getQuantityPerSourceMeta(),
                    'qty' => [
                        'arguments' => null,
                    ],
                ],
            ],
        ]);
        return $meta;
    }

    /**
     * @return array
     */
    private function getQuantityPerSourceMeta(): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'sortOrder' => 76,
                        'filter' => false,
                        'sortable' => false,
                        'label' => __('Quantity per Source'),
                        'dataType' => Text::NAME,
                        'componentType' => Column::NAME,
                        'component' => 'Magento_InventoryCatalogAdminUi/js/product/grid/cell/quantity-per-source',
                    ]
                ],
            ],
        ];
    }
}
