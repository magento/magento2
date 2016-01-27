<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Locks;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Customer\Helper\AccountManagement as AccountManagementHelper;

/**
 * Unlock Customer Controller
 */
class Unlock extends \Magento\Backend\App\Action
{
    /**
     * Account manager
     *
     * @var AccountManagementHelper
     */
    protected $accountManagementHelper;

    /**
     * Unlock constructor.
     * @param Action\Context $context
     * @param AccountManagementHelper $accountManagementHelper
     */
    public function __construct(
        Action\Context $context,
        AccountManagementHelper $accountManagementHelper
    ) {
        parent::__construct($context);
        $this->accountManagementHelper = $accountManagementHelper;
    }
    /**
     * Unlock specified customer
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        try {
            // unlock customer
            if ($customerId) {
                $this->_objectManager
                    ->get('Magento\Customer\Model\ResourceModel\LockoutManagement')
                    ->unlock($customerId);
                $this->accountManagementHelper->reindexCustomer($customerId);
                $this->getMessageManager()->addSuccess(__('Customer has been unlocked successfully.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(
            'customer/index/edit',
            ['id' => $customerId]
        );
    }
}
