<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Controller\Adminhtml\Stock;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\Framework\EntityManager\EventManager;

/**
 * Save Controller
 */
class Save extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Inventory::stock';

    /**
     * @var StockSaveProcessor
     */
    private $stockSaveProcessor;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @param Context $context
     * @param StockSaveProcessor $stockSaveProcessor
     * @param EventManager $eventManager
     */
    public function __construct(
        Context $context,
        StockSaveProcessor $stockSaveProcessor,
        EventManager $eventManager
    ) {
        parent::__construct($context);
        $this->stockSaveProcessor = $stockSaveProcessor;
        $this->eventManager = $eventManager;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $requestData = $this->getRequest()->getParams();
        if (!$this->getRequest()->isPost() || empty($requestData['general'])) {
            $this->messageManager->addErrorMessage(__('Wrong request.'));
            $this->processRedirectAfterFailureSave($resultRedirect);

            return $resultRedirect;
        }
        try {
            $stockId = isset($requestData['general'][StockInterface::STOCK_ID])
                ? (int)$requestData['general'][StockInterface::STOCK_ID]
                : null;
            $stockId = $this->stockSaveProcessor->process($stockId, $requestData);
            $this->messageManager->addSuccessMessage(__('The Stock has been saved.'));
            $this->processRedirectAfterSuccessSave($resultRedirect, $stockId);
        } catch (ValidationException $e) {
            foreach ($e->getErrors() as $localizedError) {
                $this->messageManager->addErrorMessage($localizedError->getMessage());
            }
            $this->processRedirectAfterFailureSave($resultRedirect, $stockId);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->processRedirectAfterFailureSave($resultRedirect, $stockId ?? null);
        }

        return $resultRedirect;
    }

    /**
     * @param Redirect $resultRedirect
     * @param int $stockId
     * @return void
     */
    private function processRedirectAfterSuccessSave(Redirect $resultRedirect, int $stockId)
    {
        if ($this->getRequest()->getParam('back')) {
            $resultRedirect->setPath('*/*/edit', [
                StockInterface::STOCK_ID => $stockId,
                '_current' => true,
            ]);
        } elseif ($this->getRequest()->getParam('redirect_to_new')) {
            $resultRedirect->setPath('*/*/new', [
                '_current' => true,
            ]);
        } else {
            $resultRedirect->setPath('*/*/');
        }
    }

    /**
     * @param Redirect $resultRedirect
     * @param int|null $stockId
     * @return void
     */
    private function processRedirectAfterFailureSave(Redirect $resultRedirect, int $stockId = null)
    {
        if (null === $stockId) {
            $resultRedirect->setPath('*/*/new');
        } else {
            $resultRedirect->setPath('*/*/edit', [
                StockInterface::STOCK_ID => $stockId,
                '_current' => true,
            ]);
        }
    }
}
