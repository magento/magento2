<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryGroupedProductAdminUi\Plugin\Ui\DataProvider\Product\Form;

use Magento\GroupedProduct\Ui\DataProvider\Product\GroupedProductDataProvider;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryGroupedProductAdminUi\Model\GetQuantityInformationPerSourceBySkus;

/**
 * On multi source mode add data "Quantity Per Source" to loaded items for modal window.
 */
class AddQuantityPerSourceToProductsData
{
    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var GetQuantityInformationPerSourceBySkus
     */
    private $getQuantityInformationPerSourceBySkus;

    /**
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param GetQuantityInformationPerSourceBySkus $getQuantityInformationPerSourceBySkus
     */
    public function __construct(
        IsSingleSourceModeInterface $isSingleSourceMode,
        GetQuantityInformationPerSourceBySkus $getQuantityInformationPerSourceBySkus
    ) {
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->getQuantityInformationPerSourceBySkus = $getQuantityInformationPerSourceBySkus;
    }

    /**
     * Add data "Quantity Per Source" to items on modal window for multi source mode.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param GroupedProductDataProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetData(GroupedProductDataProvider $subject, array $result): array
    {
        if ($this->isSingleSourceMode->execute()) {
            return $result;
        }

        $skus = [];
        foreach ($result['items'] as $itemData) {
            $skus[] = $itemData['sku'];
        }

        $sourceItemsData = $this->getQuantityInformationPerSourceBySkus->execute($skus);

        foreach ($result['items'] as &$productLinkData) {
            $productLinkData['quantity_per_source'] = $sourceItemsData[$productLinkData['sku']] ?? [];
        }

        return $result;
    }
}
