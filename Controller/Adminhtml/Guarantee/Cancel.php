<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Controller\Adminhtml\Guarantee;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Signifyd\Api\GuaranteeCancelingServiceInterface;
use Magento\Signifyd\Model\Guarantee\CancelGuaranteeAbility;

/**
 * Responsible for canceling Signifyd guarantee for order.
 *
 * @see https://www.signifyd.com/docs/api/#/reference/guarantees/cancel-a-guarantee-request/get-a-case
 */
class Cancel extends Action
{
    /**
     * @var GuaranteeCancelingServiceInterface
     */
    private $cancelingService;

    /**
     * @var CancelGuaranteeAbility
     */
    private $guaranteeAbility;

    /**
     * @param Context $context
     * @param GuaranteeCancelingServiceInterface $cancelingService
     * @param CancelGuaranteeAbility $guaranteeAbility
     */
    public function __construct(
        Context $context,
        GuaranteeCancelingServiceInterface $cancelingService,
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
        $orderId = (int) $this->getRequest()->getParam('order_id');
        $resultRedirect = $this->resultRedirectFactory->create();

        if (empty($orderId)) {
            $this->messageManager->addErrorMessage(__('Order id is required.'));
            $resultRedirect->setPath('sales/order/index');
            return $resultRedirect;
        }

        $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        if ($this->guaranteeAbility->isAvailable($orderId) && $this->cancelingService->cancelForOrder($orderId)) {
            $this->messageManager->addSuccessMessage(
                __('Guarantee has been cancelled for your order.')
            );
        } else {
            $this->messageManager->addErrorMessage(
                __('Sorry, we cannot cancel Guarantee for your order.')
            );
        }

        return $resultRedirect;
    }
}
