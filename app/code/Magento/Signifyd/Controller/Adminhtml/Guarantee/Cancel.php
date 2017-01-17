<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Controller\Adminhtml\Guarantee;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Signifyd\Model\Guarantee\CancelGuaranteeAbility;
use Magento\Signifyd\Model\Guarantee\CancelingService;

/**
 * Responsible for canceling Signifyd guarantee for order.
 *
 * @see https://www.signifyd.com/docs/api/#/reference/guarantees/cancel-a-guarantee-request/get-a-case
 */
class Cancel extends Action
{
    /**
     * @var CancelingService
     */
    private $cancelingService;

    /**
     * @var CancelGuaranteeAbility
     */
    private $guaranteeAbility;

    /**
     * @param Context $context
     * @param CancelingService $cancelingService
     * @param CancelGuaranteeAbility $guaranteeAbility
     */
    public function __construct(
        Context $context,
        CancelingService $cancelingService,
        CancelGuaranteeAbility $guaranteeAbility
    ) {
        parent::__construct($context);
        $this->cancelingService = $cancelingService;
        $this->guaranteeAbility = $guaranteeAbility;
    }

    /**
     * Executes service to cancel previously submitted Signifyd guarantee for an order.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $orderId = (int)$this->getRequest()->getParam('orderId');
        $resultRedirect = $this->resultRedirectFactory->create();

        if (empty($orderId)) {
            $this->messageManager->addErrorMessage(__('Order id is required.'));
            $resultRedirect->setPath('sales/order/index');
            return $resultRedirect;
        }

        $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        if ($this->guaranteeAbility->isAvailable($orderId) && $this->cancelingService->cancelForOrder($orderId)) {
            $this->messageManager->addSuccessMessage(
                __('Guarantee has been cancelled for order.')
            );
        } else {
            $this->messageManager->addErrorMessage(
                __('Sorry, we can\'t cancel Guarantee for order.')
            );
        }

        return $resultRedirect;
    }
}
