<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Block\Adminhtml\Shipment\Items;

use Magento\Backend\Block\Template\Context;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Registry;
use Magento\Inventory\Model\SourceItem\Command\GetSourceItemsBySkuInterface;
use Magento\Sales\Block\Adminhtml\Items\Renderer\DefaultRenderer;

/**
 * This block used ONLY for TEST.
 *
 */
class Qty extends DefaultRenderer
{
    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemsBySku;

    /**
     * Qty constructor.
     * @param Context $context
     * @param StockRegistryInterface $stockRegistry
     * @param StockConfigurationInterface $stockConfiguration
     * @param Registry $registry
     * @param GetSourceItemsBySkuInterface $getSourceItemsBySku
     * @param array $data
     */
    public function __construct(
        Context $context,
        StockRegistryInterface $stockRegistry,
        StockConfigurationInterface $stockConfiguration,
        Registry $registry,
        GetSourceItemsBySkuInterface $getSourceItemsBySku,
        array $data = []
    ) {
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $data);
        $this->getSourceItemsBySku = $getSourceItemsBySku;
    }

    public function getSourcesList()
    {
        return $this->getSourceItemsBySku->execute($this->getItem()->getSku())->getItems();
    }
}
