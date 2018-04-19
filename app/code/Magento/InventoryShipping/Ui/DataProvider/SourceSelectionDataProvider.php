<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Ui\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Item;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\InventorySales\Model\StockByWebsiteIdResolver;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\InventorySourceSelection\Model\GetDefaultSourceSelectionAlgorithmCodeInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

class SourceSelectionDataProvider extends AbstractDataProvider
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var StockByWebsiteIdResolver
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
     * @var SourceSelectionServiceInterface
     */
    private $sourceSelectionService;

    /**
     * @var GetDefaultSourceSelectionAlgorithmCodeInterface
     */
    private $getDefaultSourceSelectionAlgorithmCode;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var array
     */
    private $sources = [];

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Registry $registry
     * @param StockByWebsiteIdResolver $stockByWebsiteIdResolver
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     * @param InventoryRequestInterfaceFactory $inventoryRequestFactory
     * @param SourceSelectionServiceInterface $sourceSelectionService
     * @param GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
     * @param SourceRepositoryInterface $sourceRepository
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Registry $registry,
        StockByWebsiteIdResolver $stockByWebsiteIdResolver,
        ItemRequestInterfaceFactory $itemRequestFactory,
        InventoryRequestInterfaceFactory $inventoryRequestFactory,
        SourceSelectionServiceInterface $sourceSelectionService,
        GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode,
        SourceRepositoryInterface $sourceRepository,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->registry = $registry;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->itemRequestFactory = $itemRequestFactory;
        $this->inventoryRequestFactory = $inventoryRequestFactory;
        $this->sourceSelectionService = $sourceSelectionService;
        $this->getDefaultSourceSelectionAlgorithmCode = $getDefaultSourceSelectionAlgorithmCode;
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * Disable for collection processing | ????
     *
     * @param Filter $filter
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFilter(Filter $filter)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $data = [];
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->registry->registry('current_order');
        $orderId = $order->getId();
        $websiteId = $order->getStore()->getWebsiteId();

        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getIsVirtual()
                || $orderItem->getLockedDoShip()
                || $orderItem->getHasChildren()) {
                continue;
            }

            $orderItemId = $orderItem->getId();
            //TODO: Need to add additional logic for bundle product with flag ship Together
            if ($orderItem->getParentItem() && !$orderItem->isShipSeparately()) {
                $orderItemId = $orderItem->getParentItemId();
            }

            $qty = $orderItem->getSimpleQtyToShip();
            $qty = $this->castQty($orderItem, $qty);
            $sku = $orderItem->getSku();
            $data[$orderId]['items'][] = [
                'orderItemId' => $orderItemId,
                'sku' => $sku,
                'product' => $this->getProductName($orderItem),
                'qtyToShip' => $qty,
                'sources' => $this->getSources($websiteId, $sku, $qty),
            ];
        }
        $data[$orderId]['websiteId'] = $websiteId;
        $data[$orderId]['order_id'] = $orderId;
        $data[$orderId]['sourceCodes'] = $this->sources;

        return $data;
    }

    /**
     * @param $websiteId
     * @param $sku
     * @param $qty
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getSources($websiteId, $sku, $qty)
    {
        $algorithmCode = $this->getDefaultSourceSelectionAlgorithmCode->execute();
        $stockId = (int)$this->stockByWebsiteIdResolver->get((int)$websiteId)->getStockId();

        $requestItem = $this->itemRequestFactory->create([
            'sku' => $sku,
            'qty' => $qty
        ]);
        $inventoryRequest = $this->inventoryRequestFactory->create([
            'stockId' => $stockId,
            'items' => [$requestItem]
        ]);
        $sourceSelectionResult = $this->sourceSelectionService->execute(
            $inventoryRequest,
            $algorithmCode
        );
        $result = [];
        foreach ($sourceSelectionResult->getSourceSelectionItems() as $item) {
            $sourceCode = $item->getSourceCode();
            if (!isset($this->sources[$sourceCode])) {
                $this->sources[$sourceCode] = $this->getSourceName($sourceCode);
            }
            $result[] = [
                'sourceName' => $this->sources[$sourceCode],
                'sourceCode' => $sourceCode,
                'qtyAvailable' => $item->getQtyAvailable(),
                'qtyToDeduct' => $item->getQtyToDeduct()
            ];
        }

        return $result;
    }

    /**
     * Get source name by code
     *
     * @param string $sourceCode
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSourceName(string $sourceCode): string
    {
        return $this->sourceRepository->get($sourceCode)->getName();
    }

    /**
     * Generate display product name
     * @param Item $item
     * @return null|string
     */
    protected function getProductName(Item $item)
    {
        $name = $item->getName();
        if ($parentItem = $item->getParentItem()) {
            $name = $parentItem->getName();
            $options = [];
            if ($productOptions = $parentItem->getProductOptions()) {
                if (isset($productOptions['options'])) {
                    $options = array_merge($options, $productOptions['options']);
                }
                if (isset($productOptions['additional_options'])) {
                    $options = array_merge($options, $productOptions['additional_options']);
                }
                if (isset($productOptions['attributes_info'])) {
                    $options = array_merge($options, $productOptions['attributes_info']);
                }
                if (count($options)) {
                    foreach ($options as $option) {
                        $name .= '<dd>' . $option['label'] . ': ' . $option['value'] .'</dd>';
                    }
                } else {
                    $name .= '<dd>' . $item->getName() . '</dd>';
                }
            }
        }
        $name .= '<br>' .__('SKU: ') . $item->getSku();

        return $name;
    }

    /**
     * @param Item $item
     * @param string|int|float $qty
     * @return float|int
     */
    private function castQty(Item $item, $qty)
    {
        if ($item->getIsQtyDecimal()) {
            $qty = (double)$qty;
        } else {
            $qty = (int)$qty;
        }

        return $qty > 0 ? $qty : 0;
    }
}
