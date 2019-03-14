<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Controller\Adminhtml\Stock;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Delete Controller
 */
class Delete extends Action implements HttpPostActionInterface
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_InventoryApi::stock';

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @param Context $context
     * @param StockRepositoryInterface $stockRepository
     */
    public function __construct(
        Context $context,
        StockRepositoryInterface $stockRepository
    ) {
        parent::__construct($context);
        $this->stockRepository = $stockRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $stockId = $this->getRequest()->getPost(StockInterface::STOCK_ID);
        if ($stockId === null) {
            $this->messageManager->addErrorMessage(__('Wrong request.'));
            return $resultRedirect->setPath('*/*');
        }

        try {
            $stockId = (int)$stockId;
            $this->stockRepository->deleteById($stockId);
            $this->messageManager->addSuccessMessage(__('The Stock has been deleted.'));
            $resultRedirect->setPath('*/*');
        } catch (CouldNotDeleteException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('*/*/edit', [
                StockInterface::STOCK_ID => $stockId,
                '_current' => true,
            ]);
        }

        return $resultRedirect;
    }
}
