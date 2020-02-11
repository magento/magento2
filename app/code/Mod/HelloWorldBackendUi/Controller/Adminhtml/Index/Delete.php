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
use Mod\HelloWorldBackendUi\Model\ExtraCommentsMassDelete;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Request\Http as Request;

/**
 * Customer extra comments grid delete controller.
 */
class Delete extends Action implements HttpGetActionInterface
{
    /**
     * @var ExtraCommentsMassDelete
     */
    private $extraCommentsMassDelete;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Context $context
     * @param ExtraCommentsMassDelete $extraCommentsMassDelete
     * @param Request $request
     */
    public function __construct(
        Context $context,
        ExtraCommentsMassDelete $extraCommentsMassDelete,
        Request $request
    ) {
        $this->extraCommentsMassDelete = $extraCommentsMassDelete;
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * Delete action.
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $extraCommentsDeleteIds[] = $this->request->getParam('comment_id');
            $done = count($extraCommentsDeleteIds);
            $this->extraCommentsMassDelete->execute($extraCommentsDeleteIds);

            if ($done) {
                $this->messageManager->addSuccess(__('A total of %1 record were deleted.', $done));
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
        return true;
    }
}
