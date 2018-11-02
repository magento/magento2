<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Model\InventoryRequestBuilder;

use Magento\Framework\App\RequestInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventoryShippingAdminUi\Model\InventoryRequestBuilderInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;

/**
 * @inheritdoc
 */
class DefaultInventoryRequestBuilder implements InventoryRequestBuilderInterface
{
    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var ItemRequestInterfaceFactory
     */
    private $itemRequestFactory;

    /**
     * @var InventoryRequestInterfaceFactory
     */
    private $inventoryRequestFactory;

    /**
     * Priority constructor.
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param InventoryRequestInterfaceFactory $inventoryRequestFactory
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     */
    public function __construct(
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        InventoryRequestInterfaceFactory $inventoryRequestFactory,
        ItemRequestInterfaceFactory $itemRequestFactory
    ) {
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->itemRequestFactory = $itemRequestFactory;
        $this->inventoryRequestFactory = $inventoryRequestFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(RequestInterface $request): InventoryRequestInterface
    {
        $postRequest = $request->getPost()->toArray();
        $requestData = $postRequest['requestData'];

        //TODO: maybe need to add exception when websiteId empty
        $websiteId = $postRequest['websiteId'] ?? 1;
        $stockId = (int) $this->stockByWebsiteIdResolver->execute((int)$websiteId)->getStockId();

        $requestItems = [];
        foreach ($requestData as $data) {
            $requestItems[] = $this->itemRequestFactory->create([
                'sku' => $data['sku'],
                'qty' => $data['qty']
            ]);
        }
        $inventoryRequest = $this->inventoryRequestFactory->create([
            'stockId' => $stockId,
            'items'   => $requestItems
        ]);

        return $inventoryRequest;
    }
}
