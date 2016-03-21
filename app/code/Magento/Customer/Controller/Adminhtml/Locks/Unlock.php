<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Locks;

use Magento\Customer\Model\AuthenticationInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

/**
 * Unlock Customer Controller
 */
class Unlock extends \Magento\Backend\App\Action
{
    /**
     * Authentication
     *
     * @var AuthenticationInterface
     */
    protected $authentication;

    /**
     * Unlock constructor.
     *
     * @param Action\Context $context
     * @param AuthenticationInterface $authentication
     */
    public function __construct(
        Action\Context $context,
        AuthenticationInterface $authentication
    ) {
        parent::__construct($context);
        $this->authentication = $authentication;
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
                $this->authentication->unlock($customerId);
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
