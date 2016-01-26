<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Locks;

use Magento\Framework\Controller\ResultFactory;

/**
 * Unlock Customer Controller
 */
class Unlock extends \Magento\Backend\App\Action
{
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
