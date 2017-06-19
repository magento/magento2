<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Controller\Adminhtml\Source;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

/**
 * Class Save
 */
class Save extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Inventory::source';

    /**
     * Registry source_id key
     */
    const REGISTRY_SOURCE_ID_KEY = 'source_id';

    /**
     * @var SourceInterfaceFactory
     */
    private $sourceFactory;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CarrierRequestDataHydrator
     */
    private $carrierRequestDataHydrator;

    /**
     * @param Context $context
     * @param SourceInterfaceFactory $sourceFactory
     * @param SourceRepositoryInterface $sourceRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param CarrierRequestDataHydrator $carrierRequestDataHydrator
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        SourceInterfaceFactory $sourceFactory,
        SourceRepositoryInterface $sourceRepository,
        DataObjectHelper $dataObjectHelper,
        Registry $registry,
        CarrierRequestDataHydrator $carrierRequestDataHydrator
    ) {
        parent::__construct($context);
        $this->sourceFactory = $sourceFactory;
        $this->sourceRepository = $sourceRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->registry = $registry;
        $this->carrierRequestDataHydrator = $carrierRequestDataHydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $requestData = $this->getRequest()->getParam('general');
        if ($this->getRequest()->isPost() && $requestData) {
            try {
                $sourceId = !empty($requestData[SourceInterface::SOURCE_ID])
                    ? $requestData[SourceInterface::SOURCE_ID] : null;

                $sourceId = $this->processSave($sourceId, $requestData);
                // Keep data for plugins on Save controller. Now we can not call separate services from one form.
                $this->registry->register(self::REGISTRY_SOURCE_ID_KEY, $sourceId);

                $this->messageManager->addSuccessMessage(__('The Source has been saved.'));
                $this->processRedirectAfterSuccessSave($resultRedirect, $sourceId);

            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('The Source does not exist.'));
                $this->processRedirectAfterFailureSave($resultRedirect);
            } catch (CouldNotSaveException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->processRedirectAfterFailureSave($resultRedirect, $sourceId);
            } catch (InputException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->processRedirectAfterFailureSave($resultRedirect, $sourceId);
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Could not save source'));
                $this->processRedirectAfterFailureSave($resultRedirect, $sourceId);
            }
        } else {
            $this->messageManager->addErrorMessage(__('Wrong request.'));
            $this->processRedirectAfterFailureSave($resultRedirect);
        }
        return $resultRedirect;
    }

    /**
     * @param int $sourceId
     * @param array $requestData
     * @return int
     */
    private function processSave($sourceId, array $requestData)
    {
        if ($sourceId) {
            $source = $this->sourceRepository->get($sourceId);
        } else {
            /** @var SourceInterface $source */
            $source = $this->sourceFactory->create();
        }
        $source = $this->dataObjectHelper->populateWithArray($source, $requestData, SourceInterface::class);
        $source = $this->carrierRequestDataHydrator->hydrate($source, $requestData);

        $sourceId = $this->sourceRepository->save($source);
        return $sourceId;
    }

    /**
     * @param Redirect $resultRedirect
     * @param int $sourceId
     * @return void
     */
    private function processRedirectAfterSuccessSave(Redirect $resultRedirect, $sourceId)
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
    private function processRedirectAfterFailureSave(Redirect $resultRedirect, $sourceId = null)
    {
        if (null === $sourceId) {
            $resultRedirect->setPath('*/*/');
        } else {
            $resultRedirect->setPath('*/*/edit', [
                SourceInterface::SOURCE_ID => $sourceId,
                '_current' => true,
            ]);
        }
    }
}
