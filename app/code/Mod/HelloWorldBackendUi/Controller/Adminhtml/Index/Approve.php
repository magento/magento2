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
use Mod\HelloWorldBackendUi\Model\ExtraCommentsMassApprove;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Request\Http as Request;

/**
 * Customer extra comments grid approve controller.
 */
class Approve extends Action implements HttpGetActionInterface
{
    /**
     * @var ExtraCommentsMassApprove
     */
    private $extraCommentsMassApprove;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Context $context
     * @param ExtraCommentsMassApprove $extraCommentsMassApprove
     * @param Request $request
     */
    public function __construct(
        Context $context,
        ExtraCommentsMassApprove $extraCommentsMassApprove,
        Request $request
    ) {
        $this->extraCommentsMassApprove = $extraCommentsMassApprove;
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * Approve action.
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $extraCommentsApproveIds[] = $this->request->getParam('comment_id');
            $done = count($extraCommentsApproveIds);
            $this->extraCommentsMassApprove->execute($extraCommentsApproveIds);

            if ($done) {
                $this->messageManager->addSuccess(__('A total of %1 record were approved.', $done));
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
