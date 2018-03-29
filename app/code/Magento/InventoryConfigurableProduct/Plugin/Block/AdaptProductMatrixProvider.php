<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Plugin\Block;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Variations\Config\ProductMatrixProvider;
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;
use Magento\InventoryConfigurableProduct\Model\GetQuantityInformationPerSource;

/**
 * Add value for field "quantityPerSource" for grid "Associated Products" and "Disassociated Products"
 * on step "Summary".
 */
class AdaptProductMatrixProvider
{
    /**
     * @var GetQuantityInformationPerSource
     */
    private $getQuantityInformationPerSource;

    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @param GetQuantityInformationPerSource $getQuantityInformationPerSource
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     */
    public function __construct(
        GetQuantityInformationPerSource $getQuantityInformationPerSource,
        IsSingleSourceModeInterface $isSingleSourceMode
    ) {
        $this->getQuantityInformationPerSource = $getQuantityInformationPerSource;
        $this->isSingleSourceMode = $isSingleSourceMode;
    }

    /**
     * @param ProductMatrixProvider $subject
     * @param array $result
     * @param ProductInterface $product
     * @param array $variationOptions
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        ProductMatrixProvider $subject,
        array $result,
        ProductInterface $product,
        array $variationOptions
    ): array {
        if ($this->isSingleSourceMode->execute() === false) {
            $result['quantityPerSource'] = $this->getQuantityInformationPerSource->execute($product->getSku());
        }

        return $result;
    }
}
