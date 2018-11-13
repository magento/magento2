<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Model\InventoryRequestBuilder;

use Magento\Framework\App\RequestInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventoryShippingAdminUi\Model\InventoryRequestBuilderFromOrderInterface;
use Magento\InventorySourceSelection\Model\GetAddressRequestFromOrder;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestExtensionInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\Sales\Api\OrderItemRepositoryInterface;

/**
 * @inheritdoc
 */
class DistanceInventoryRequestBuilderFromOrder implements InventoryRequestBuilderFromOrderInterface
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
     * @var GetAddressRequestFromOrder
     */
    private $getAddressRequestFromOrder;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var InventoryRequestExtensionInterfaceFactory
     */
    private $inventoryRequestExtensionInterfaceFactory;

    /**
     * Priority constructor.
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param InventoryRequestInterfaceFactory $inventoryRequestFactory
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param GetAddressRequestFromOrder $getAddressRequestFromOrder
     * @param InventoryRequestExtensionInterfaceFactory $inventoryRequestExtensionInterfaceFactory
     */
    public function __construct(
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        InventoryRequestInterfaceFactory $inventoryRequestFactory,
        ItemRequestInterfaceFactory $itemRequestFactory,
        OrderItemRepositoryInterface $orderItemRepository,
        GetAddressRequestFromOrder $getAddressRequestFromOrder,
        InventoryRequestExtensionInterfaceFactory $inventoryRequestExtensionInterfaceFactory
    ) {
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->itemRequestFactory = $itemRequestFactory;
        $this->inventoryRequestFactory = $inventoryRequestFactory;
        $this->getAddressRequestFromOrder = $getAddressRequestFromOrder;
        $this->orderItemRepository = $orderItemRepository;
        $this->inventoryRequestExtensionInterfaceFactory = $inventoryRequestExtensionInterfaceFactory;
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

        $orderItemId = (int) $requestData[0]['orderItem'];
        $orderItem = $this->orderItemRepository->get($orderItemId);

        $address = $this->getAddressRequestFromOrder->execute((int) $orderItem->getOrderId());

        $inventoryRequest = $this->inventoryRequestFactory->create([
            'stockId' => $stockId,
            'items'   => $requestItems
        ]);

        $extensionAttributes = $this->inventoryRequestExtensionInterfaceFactory->create();
        $extensionAttributes->setDestinationAddress($address);
        $inventoryRequest->setExtensionAttributes($extensionAttributes);

        return $inventoryRequest;
    }
}
