<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductAdminUi\Plugin\Block;

use Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Variations\Config\Matrix;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryConfigurableProductAdminUi\Model\GetQuantityInformationPerSource;

/**
 * Add value for field "quantityPerSource" for grid "Associated Products" and "Disassociated Products"
 * on step "Summary".
 */
class AddQuantityPerSourceToVariationsMatrix
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
     * @param Matrix $subject
     * @param $result
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductMatrix(
        Matrix $subject,
        $result
    ) {
        if ($this->isSingleSourceMode->execute() === false && is_array($result)) {
            foreach ($result as $key => $variation) {
                $result[$key]['quantityPerSource'] = $this->getQuantityInformationPerSource->execute($variation['sku']);
            }
        }

        return $result;
    }
}
