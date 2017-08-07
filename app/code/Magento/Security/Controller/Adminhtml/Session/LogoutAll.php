<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Controller\Adminhtml\Session;

use Magento\Backend\App\Action\Context;
use Magento\Security\Model\AdminSessionsManager;

/**
 * Admin session logout all
 * @since 2.1.0
 */
class LogoutAll extends \Magento\Backend\App\Action
{
    /**
     * @var AdminSessionsManager
     * @since 2.1.0
     */
    protected $sessionsManager;

    /**
     * Check constructor.
     * @param Context $context
     * @param AdminSessionsManager $sessionsManager
     * @since 2.1.0
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
     * @since 2.1.0
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
