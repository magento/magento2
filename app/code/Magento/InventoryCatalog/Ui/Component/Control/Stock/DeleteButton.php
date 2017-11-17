<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Ui\Component\Control\Stock;

use Magento\Backend\Ui\Component\Control\DeleteButton as StockDeleteButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Represents delete button with pre-configured options
 * Provide an ability to show delete button only when stock id is not default
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
     * @var RequestInterface
     */
    private $request;

    /**
     * @param StockDeleteButton $deleteButton
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param RequestInterface $request
     */
    public function __construct(
        StockDeleteButton $deleteButton,
        DefaultStockProviderInterface $defaultStockProvider,
        RequestInterface $request
    ) {
        $this->deleteButton = $deleteButton;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        if ((int)$this->request->getParam(StockInterface::STOCK_ID) === $this->defaultStockProvider->getId()) {
            return [];
        }

        return $this->deleteButton->getButtonData();
    }
}
