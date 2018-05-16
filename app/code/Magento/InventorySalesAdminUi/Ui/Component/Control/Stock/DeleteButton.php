<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesAdminUi\Ui\Component\Control\Stock;

use Magento\Backend\Ui\Component\Control\DeleteButton as StockDeleteButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\InventorySalesApi\Model\GetAssignedSalesChannelsForStockInterface;

/**
 * Represents delete button with pre-configured options
 * Provide an ability to show delete button only when stock id is not default or doesn't have assigned sales channels
 */
class DeleteButton implements ButtonProviderInterface
{
    /**
     * @var StockDeleteButton
     */
    private $deleteButton;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var GetAssignedSalesChannelsForStockInterface
     */
    private $assignedSalesChannelsForStock;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param StockDeleteButton $deleteButton
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param GetAssignedSalesChannelsForStockInterface $assignedSalesChannelsForStock
     * @param RequestInterface $request
     */
    public function __construct(
        StockDeleteButton $deleteButton,
        DefaultStockProviderInterface $defaultStockProvider,
        GetAssignedSalesChannelsForStockInterface $assignedSalesChannelsForStock,
        RequestInterface $request
    ) {
        $this->deleteButton = $deleteButton;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->assignedSalesChannelsForStock = $assignedSalesChannelsForStock;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $stockId = (int)$this->request->getParam(StockInterface::STOCK_ID);
        $assignSalesChannels = $this->assignedSalesChannelsForStock->execute($stockId);
        if ($stockId === $this->defaultStockProvider->getId() || count($assignSalesChannels)) {
            return [];
        }

        return $this->deleteButton->getButtonData();
    }
}
