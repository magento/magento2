<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Controller\Adminhtml\Source;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\StateException;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
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
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Context $context
     * @param SourceInterfaceFactory $sourceFactory
     * @param SourceRepositoryInterface $sourceRepository
     * @param HydratorInterface $hydrator
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        SourceInterfaceFactory $sourceFactory,
        SourceRepositoryInterface $sourceRepository,
        HydratorInterface $hydrator,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->sourceFactory = $sourceFactory;
        $this->sourceRepository = $sourceRepository;
        $this->hydrator = $hydrator;
        $this->registry = $registry;
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

                if ($sourceId) {
                    $source = $this->sourceRepository->get($sourceId);
                } else {
                    /** @var SourceInterface $source */
                    $source = $this->sourceFactory->create();
                }
                $source = $this->hydrator->hydrate($source, $requestData);
                $sourceId = $this->sourceRepository->save($source);
                // Keep data for plugins on Save controller. Now we can not call separate services from one form.
                $this->registry->register(self::REGISTRY_SOURCE_ID_KEY, $sourceId);

                $this->messageManager->addSuccessMessage(__('The Source has been saved.'));
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
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('The Source does not exist.'));
                $resultRedirect->setPath('*/*/');
            } catch (StateException|CouldNotSaveException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                if (empty($sourceId)) {
                    $resultRedirect->setPath('*/*/');
                } else {
                    $resultRedirect->setPath('*/*/edit', [
                        SourceInterface::SOURCE_ID => $sourceId,
                        '_current' => true,
                    ]);
                }
            }
        } else {
            $this->messageManager->addErrorMessage(__('Wrong request.'));
            $resultRedirect->setPath('*/*');
        }
        return $resultRedirect;
    }
}
