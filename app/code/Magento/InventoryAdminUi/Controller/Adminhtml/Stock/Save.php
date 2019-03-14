<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Controller\Adminhtml\Stock;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryAdminUi\Model\Stock\StockSaveProcessor;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Save Controller
 */
class Save extends Action implements HttpPostActionInterface
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_InventoryApi::stock';

    /**
     * @var StockSaveProcessor
     */
    private $stockSaveProcessor;

    /**
     * @param Context $context
     * @param StockSaveProcessor $stockSaveProcessor
     */
    public function __construct(
        Context $context,
        StockSaveProcessor $stockSaveProcessor
    ) {
        parent::__construct($context);
        $this->stockSaveProcessor = $stockSaveProcessor;
    }

    /**
     * @inheritdoc
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $request = $this->getRequest();
        $requestData = $request->getParams();
        if (!$request->isPost() || empty($requestData['general'])) {
            $this->messageManager->addErrorMessage(__('Wrong request.'));
            $this->processRedirectAfterFailureSave($resultRedirect);
            return $resultRedirect;
        }
        return $this->processSave($requestData, $request, $resultRedirect);
    }

    /**
     * @param array $requestData
     * @param RequestInterface $request
     * @param Redirect $resultRedirect
     * @return ResultInterface
     */
    private function processSave(
        array $requestData,
        RequestInterface $request,
        Redirect $resultRedirect
    ): ResultInterface {
        try {
            $stockId = isset($requestData['general'][StockInterface::STOCK_ID])
                ? (int)$requestData['general'][StockInterface::STOCK_ID]
                : null;

            $stockId = $this->stockSaveProcessor->process($stockId, $request);

            $this->messageManager->addSuccessMessage(__('The Stock has been saved.'));
            $this->processRedirectAfterSuccessSave($resultRedirect, $stockId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('The Stock does not exist.'));
            $this->processRedirectAfterFailureSave($resultRedirect);
        } catch (ValidationException $e) {
            foreach ($e->getErrors() as $localizedError) {
                $this->messageManager->addErrorMessage($localizedError->getMessage());
            }
            $this->processRedirectAfterFailureSave($resultRedirect, $stockId);
        } catch (CouldNotSaveException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->processRedirectAfterFailureSave($resultRedirect, $stockId);
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->processRedirectAfterFailureSave($resultRedirect, $stockId);
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not save Stock.'));
            $this->processRedirectAfterFailureSave($resultRedirect, $stockId ?? null);
        }
        return $resultRedirect;
    }

    /**
     * @param Redirect $resultRedirect
     * @param int $stockId
     *
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
     *
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
