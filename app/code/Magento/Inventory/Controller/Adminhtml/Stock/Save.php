<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Controller\Adminhtml\Stock;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\Data\StockInterfaceFactory;
use Magento\InventoryApi\Api\StockRepositoryInterface;

class Save extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Inventory::stock';

    /**
     * Registry stock_id key
     */
    const REGISTRY_STOCK_ID_KEY = 'stock_id';

    /**
     * @var StockInterfaceFactory
     */
    private $stockFactory;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Context $context
     * @param StockInterfaceFactory $stockFactory
     * @param StockRepositoryInterface $stockRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        StockInterfaceFactory $stockFactory,
        StockRepositoryInterface $stockRepository,
        DataObjectHelper $dataObjectHelper,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->stockFactory = $stockFactory;
        $this->stockRepository = $stockRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $requestData = $this->getRequest()->getParam('general');
        if ($this->getRequest()->isPost() && $requestData) {
            try {
                $stockId = $requestData[StockInterface::STOCK_ID]??null;

                $stockId = $this->processSave($stockId, $requestData);
                // Keep data for plugins on Save controller. Now we can not call separate services from one form.
                $this->registry->register(self::REGISTRY_STOCK_ID_KEY, $stockId);

                $this->messageManager->addSuccessMessage(__('The Stock has been saved.'));
                $this->processRedirectAfterSuccessSave($resultRedirect, $stockId);

            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('The Stock does not exist.'));
                $this->processRedirectAfterFailureSave($resultRedirect);
            } catch (CouldNotSaveException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->processRedirectAfterFailureSave($resultRedirect, $stockId);
            } catch (InputException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->processRedirectAfterFailureSave($resultRedirect, $stockId);
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Could not save stock'));
                $this->processRedirectAfterFailureSave($resultRedirect, $stockId);
            }
        } else {
            $this->messageManager->addErrorMessage(__('Wrong request.'));
            $this->processRedirectAfterFailureSave($resultRedirect);
        }
        return $resultRedirect;
    }

    /**
     * Saves inventory stock and returns stock id
     *
     * @param int $stockId
     * @param array $requestData
     * @return int
     */
    private function processSave($stockId, array $requestData)
    {
        if ($stockId) {
            $stock = $this->stockRepository->get($stockId);
        } else {
            /** @var StockInterface $stock */
            $stock = $this->stockFactory->create();
        }
        $this->dataObjectHelper->populateWithArray($stock, $requestData, StockInterface::class);

        $stockId = $this->stockRepository->save($stock);
        return $stockId;
    }

    /**
     * @param Redirect $resultRedirect
     * @param int $stockId
     * @return void
     */
    private function processRedirectAfterSuccessSave(Redirect $resultRedirect, $stockId)
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
    private function processRedirectAfterFailureSave(Redirect $resultRedirect, $stockId = null)
    {
        if (null === $stockId) {
            $resultRedirect->setPath('*/*/');
        } else {
            $resultRedirect->setPath('*/*/edit', [
                StockInterface::STOCK_ID => $stockId,
                '_current' => true,
            ]);
        }
    }
}
