<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Controller\Adminhtml\Source;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Edit Controller
 */
class Edit extends Action implements HttpGetActionInterface
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_InventoryApi::source';

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
    public function execute(): ResultInterface
    {
        $sourceCode = $this->getRequest()->getParam(SourceInterface::SOURCE_CODE);
        try {
            $source = $this->sourceRepository->get($sourceCode);

            /** @var Page $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $result->setActiveMenu('Magento_InventoryApi::source')
                ->addBreadcrumb(__('Edit Source'), __('Edit Source'));
            $result->getConfig()
                ->getTitle()
                ->prepend(__('Edit Source: %name', ['name' => $source->getName()]));
        } catch (NoSuchEntityException $e) {
            /** @var Redirect $result */
            $result = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(
                __('Source with source code "%value" does not exist.', ['value' => $sourceCode])
            );
            $result->setPath('*/*');
        }

        return $result;
    }
}
