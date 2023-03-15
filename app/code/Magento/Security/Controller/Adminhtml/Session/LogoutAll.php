<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Controller\Adminhtml\Session;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Security\Model\AdminSessionsManager;

/**
 * Admin session logout all
 */
class LogoutAll extends Action
{
    /**
     * Check constructor.
     * @param Context $context
     * @param AdminSessionsManager $sessionsManager
     */
    public function __construct(
        Context $context,
        protected readonly AdminSessionsManager $sessionsManager
    ) {
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            $this->sessionsManager->logoutOtherUserSessions();
            $this->messageManager->addSuccessMessage(__('All other open sessions for this account were terminated.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __("We couldn't logout because of an error."));
        }
        $this->_redirect('*/*/activity');
    }
}
