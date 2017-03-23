<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Controller\Adminhtml\Session;

use Magento\Backend\App\Action\Context;
use Magento\Security\Model\AdminSessionsManager;

/**
 * Admin session logout all
 */
class LogoutAll extends \Magento\Backend\App\Action
{
    /**
     * @var AdminSessionsManager
     */
    protected $sessionsManager;

    /**
     * Check constructor.
     * @param Context $context
     * @param AdminSessionsManager $sessionsManager
     */
    public function __construct(
        Context $context,
        AdminSessionsManager $sessionsManager
    ) {
        parent::__construct($context);
        $this->sessionsManager = $sessionsManager;
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            $this->sessionsManager->logoutOtherUserSessions();
            $this->messageManager->addSuccess(__('All other open sessions for this account were terminated.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __("We couldn't logout because of an error."));
        }
        $this->_redirect('*/*/activity');
    }
}
