<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Ui\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Order\Item;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\Framework\App\RequestInterface;
use Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface;

class SourceSelectionDataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var GetSkuFromOrderItemInterface
     */
    private $getSkuFromOrderItem;

    /**
     * @var array
     */
    private $sources = [];

    /**
     * @var GetSourcesByOrderIdSkuAndQty
     */
    private $getSourcesByOrderIdSkuAndQty;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RequestInterface $request
     * @param OrderRepository $orderRepository
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param null $getSourcesByStockIdSkuAndQty @deprecated
     * @param GetSkuFromOrderItemInterface $getSkuFromOrderItem
     * @param GetSourcesByOrderIdSkuAndQty $getSourcesByOrderIdSkuAndQty
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.LongVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        OrderRepository $orderRepository,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        $getSourcesByStockIdSkuAndQty,
        GetSkuFromOrderItemInterface $getSkuFromOrderItem,
        GetSourcesByOrderIdSkuAndQty $getSourcesByOrderIdSkuAndQty = null,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->getSkuFromOrderItem = $getSkuFromOrderItem;
        $this->getSourcesByOrderIdSkuAndQty = $getSourcesByOrderIdSkuAndQty ?:
            ObjectManager::getInstance()->get(GetSourcesByOrderIdSkuAndQty::class);
    }

    /**
     * Disable for collection processing
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
        $orderId = (int) $this->request->getParam('order_id');
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);
        $websiteId = $order->getStore()->getWebsiteId();
        $stockId = (int)$this->stockByWebsiteIdResolver->execute((int)$websiteId)->getStockId();

        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getIsVirtual()
                || $orderItem->getLockedDoShip()
                || $orderItem->getHasChildren()) {
                continue;
            }

            $item = $orderItem->isDummy(true) ? $orderItem->getParentItem() : $orderItem;
            $qty = $item->getSimpleQtyToShip();
            $qty = $this->castQty($item, $qty);
            $sku = $this->getSkuFromOrderItem->execute($item);
            $data[$orderId]['items'][] = [
                'orderItemId' => $item->getId(),
                'sku' => $sku,
                'product' => $this->getProductName($orderItem),
                'qtyToShip' => $qty,
                'sources' => $this->getSources($orderId, $sku, $qty),
                'isManageStock' => $this->isManageStock($sku, $stockId)
            ];
        }
        $data[$orderId]['websiteId'] = $websiteId;
        $data[$orderId]['order_id'] = $orderId;
        foreach ($this->sources as $code => $name) {
            $data[$orderId]['sourceCodes'][] = [
                'value' => $code,
                'label' => $name
            ];
        }

        return $data;
    }

    /**
     * Get sources
     *
     * @param int $orderId
     * @param string $sku
     * @param float $qty
     * @return array
     * @throws NoSuchEntityException
     */
    private function getSources(int $orderId, string $sku, float $qty): array
    {
        $sources = $this->getSourcesByOrderIdSkuAndQty->execute($orderId, $sku, $qty);
        foreach ($sources as $source) {
            $this->sources[$source['sourceCode']] = $source['sourceName'];
        }
        return $sources;
    }

    /**
     * @param $itemSku
     * @param $stockId
     * @return bool
     * @throws LocalizedException
     */
    private function isManageStock($itemSku, $stockId)
    {
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($itemSku, $stockId);

        return $stockItemConfiguration->isManageStock();
    }

    /**
     * Generate display product name
     * @param Item $item
     * @return null|string
     */
    private function getProductName(Item $item)
    {
        //TODO: need to transfer this to html block and render on Ui
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
