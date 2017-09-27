<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Controller\Adminhtml\Source;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Edit Controller
 */
class Edit extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = Index::ADMIN_RESOURCE;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @param Context $context
     * @param SourceRepositoryInterface $sourceRepository
     */
    public function __construct(
        Context $context,
        SourceRepositoryInterface $sourceRepository
    ) {
        parent::__construct($context);
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $sourceId = $this->getRequest()->getParam(SourceInterface::SOURCE_ID);
        try {
            $source = $this->sourceRepository->get($sourceId);

            /** @var Page $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $result->setActiveMenu('Magento_Inventory::source')
                ->addBreadcrumb(__('Edit Source'), __('Edit Source'));
            $result->getConfig()
                ->getTitle()
                ->prepend(__('Edit Source: %name', ['name' => $source->getName()]));
        } catch (NoSuchEntityException $e) {
            /** @var Redirect $result */
            $result = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(
                __('Source with id "%value" does not exist.', ['value' => $sourceId])
            );
            $result->setPath('*/*');
        }
        return $result;
    }
}
