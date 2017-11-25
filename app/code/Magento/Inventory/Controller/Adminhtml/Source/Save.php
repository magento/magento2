<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Controller\Adminhtml\Source;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

/**
 * Save Controller
 */
class Save extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Inventory::source';

    /**
     * @var SourceInterfaceFactory
     */
    private $sourceFactory;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var SourceHydrator
     */
    private $sourceHydrator;

    /**
     * @param Context $context
     * @param SourceInterfaceFactory $sourceFactory
     * @param SourceRepositoryInterface $sourceRepository
     * @param SourceHydrator $sourceHydrator
     */
    public function __construct(
        Context $context,
        SourceInterfaceFactory $sourceFactory,
        SourceRepositoryInterface $sourceRepository,
        SourceHydrator $sourceHydrator
    ) {
        parent::__construct($context);
        $this->sourceFactory = $sourceFactory;
        $this->sourceRepository = $sourceRepository;
        $this->sourceHydrator = $sourceHydrator;
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
                $sourceId = isset($requestData['general'][SourceInterface::SOURCE_ID])
                    ? (int)$requestData['general'][SourceInterface::SOURCE_ID]
                    : null;
                $sourceId = $this->processSave($requestData, $sourceId);

                $this->messageManager->addSuccessMessage(__('The Source has been saved.'));
                $this->processRedirectAfterSuccessSave($resultRedirect, $sourceId);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('The Source does not exist.'));
                $this->processRedirectAfterFailureSave($resultRedirect);
            } catch (ValidationException $e) {
                foreach ($e->getErrors() as $localizedError) {
                    $this->messageManager->addErrorMessage($localizedError->getMessage());
                }
                $this->processRedirectAfterFailureSave($resultRedirect, $sourceId);
            } catch (CouldNotSaveException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->processRedirectAfterFailureSave($resultRedirect, $sourceId);
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Could not save source.'));
                $this->processRedirectAfterFailureSave($resultRedirect, $sourceId ?? null);
            }
        } else {
            $this->messageManager->addErrorMessage(__('Wrong request.'));
            $this->processRedirectAfterFailureSave($resultRedirect);
        }
        return $resultRedirect;
    }

    /**
     * @param array $requestData
     * @param int|null $sourceId
     * @return int
     */
    private function processSave(array $requestData, int $sourceId = null): int
    {
        if (null === $sourceId) {
            /** @var SourceInterface $source */
            $source = $this->sourceFactory->create();
        } else {
            $source = $this->sourceRepository->get($sourceId);
        }
        $source = $this->sourceHydrator->hydrate($source, $requestData);

        $this->_eventManager->dispatch(
            'save_source_controller_populate_source_with_data',
            [
                'request' => $this->getRequest(),
                'source' => $source,
            ]
        );

        $sourceId = $this->sourceRepository->save($source);

        $this->_eventManager->dispatch(
            'controller_action_inventory_source_save_after',
            [
                'request' => $this->getRequest(),
                'source' => $source,
            ]
        );

        return $sourceId;
    }

    /**
     * @param Redirect $resultRedirect
     * @param int $sourceId
     * @return void
     */
    private function processRedirectAfterSuccessSave(Redirect $resultRedirect, int $sourceId)
    {
        if ($this->getRequest()->getParam('back')) {
            $resultRedirect->setPath('*/*/edit', [
                SourceInterface::SOURCE_ID => $sourceId,
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
     * @param int|null $sourceId
     * @return void
     */
    private function processRedirectAfterFailureSave(Redirect $resultRedirect, int $sourceId = null)
    {
        if (null === $sourceId) {
            $resultRedirect->setPath('*/*/new');
        } else {
            $resultRedirect->setPath('*/*/edit', [
                SourceInterface::SOURCE_ID => $sourceId,
                '_current' => true,
            ]);
        }
    }
}
