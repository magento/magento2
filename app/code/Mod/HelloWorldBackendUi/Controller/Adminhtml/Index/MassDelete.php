<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldBackendUi\Controller\Adminhtml\Index;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Ui\Component\MassAction\Filter;
use Mod\HelloWorldBackendUi\Model\ResourceModel\Grid\Grid\CollectionFactory;
use Mod\HelloWorldBackendUi\Model\ExtraCommentsMassDelete;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Mass delete controller.
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ExtraCommentsMassDelete
     */
    private $extraCommentsMassDelete;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ExtraCommentsMassDelete $extraCommentsMassDelete
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ExtraCommentsMassDelete $extraCommentsMassDelete
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->extraCommentsMassDelete = $extraCommentsMassDelete;

        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $extraCommentsMassDeleteIds = $collection->getAllIds();
            $done = count($extraCommentsMassDeleteIds);
            $this->extraCommentsMassDelete->execute($extraCommentsMassDeleteIds);

            if ($done) {
                $this->messageManager->addSuccess(__('A total of %1 record(s) were deleted.', $done));
            }
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * Check for allow.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mod_HelloWorldBackendUi::mass_massDelete');
    }
}
