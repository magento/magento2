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
use Magento\Framework\EntityManager\EventManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\Data\StockInterfaceFactory;
use Magento\InventoryApi\Api\StockRepositoryInterface;

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
     * @var StockSourceLinkProcessor
     */
    private $stockSourceLinkProcessor;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @param Context $context
     * @param StockInterfaceFactory $stockFactory
     * @param StockRepositoryInterface $stockRepository
     * @param DataObjectHelper $sourceHydrator
     * @param StockSourceLinkProcessor $stockSourceLinkProcessor
     * @param EventManager $eventManager
     */
    public function __construct(
        Context $context,
        StockInterfaceFactory $stockFactory,
        StockRepositoryInterface $stockRepository,
        DataObjectHelper $sourceHydrator,
        StockSourceLinkProcessor $stockSourceLinkProcessor,
        EventManager $eventManager
    ) {
        parent::__construct($context);
        $this->stockFactory = $stockFactory;
        $this->stockRepository = $stockRepository;
        $this->dataObjectHelper = $sourceHydrator;
        $this->stockSourceLinkProcessor = $stockSourceLinkProcessor;
        $this->eventManager = $eventManager;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $requestData = $this->getRequest()->getParams();
        if ($this->getRequest()->isPost() && !empty($requestData['general'])) {
            try {
                $stockId = isset($requestData['general'][StockInterface::STOCK_ID])
                    ? (int)$requestData['general'][StockInterface::STOCK_ID]
                    : null;
                $stockId = $this->processSave($requestData, $stockId);

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
                $this->messageManager->addErrorMessage(__('Could not save stock.'));
                $this->processRedirectAfterFailureSave($resultRedirect, $stockId ?? null);
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
    private function processSave(array $requestData, int $stockId = null): int
    {
        if (null === $stockId) {
            /** @var StockInterface $stock */
            $stock = $this->stockFactory->create();
        } else {
            $stock = $this->stockRepository->get($stockId);
        }
        $this->dataObjectHelper->populateWithArray($stock, $requestData['general'], StockInterface::class);
        $this->eventManager->dispatch(
            'save_stock_controller_populate_stock_with_data',
            [
                'request_data' => $requestData,
                'stock' => $stock,
            ]
        );
        $stockId = $this->stockRepository->save($stock);

        $assignedSources =
            isset($requestData['sources']['assigned_sources']) && is_array($requestData['sources']['assigned_sources'])
            ? $requestData['sources']['assigned_sources']
            : [];
        $this->stockSourceLinkProcessor->process($stockId, $assignedSources);
        return $stockId;
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
