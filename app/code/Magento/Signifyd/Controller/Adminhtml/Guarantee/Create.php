<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Controller\Adminhtml\Guarantee;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Signifyd\Model\Guarantee\CreateGuaranteeAbility;
use Magento\Signifyd\Model\Guarantee\CreationService;

/**
 * Responsible for submitting order for Guarantee.
 *
 * @see https://www.signifyd.com/docs/api/#/reference/guarantees/create-guarantee
 */
class Create extends Action
{
    /**
     * @var CreationService
     */
    private $creationService;

    /**
     * @var CreateGuaranteeAbility
     */
    private $createGuaranteeAbility;

    /**
     * @param Context $context
     * @param CreationService $creationService
     * @param CreateGuaranteeAbility $createGuaranteeAbility
     */
    public function __construct(
        Context $context,
        CreationService $creationService,
        CreateGuaranteeAbility $createGuaranteeAbility
    ) {
        parent::__construct($context);
        $this->creationService = $creationService;
        $this->createGuaranteeAbility = $createGuaranteeAbility;
    }

    /**
     * Submits order for Guarantee and redirects user to order page with result message
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        $resultRedirect = $this->resultRedirectFactory->create();

        if (empty($orderId)) {
            $this->messageManager->addErrorMessage(__('Order id is required.'));
            $resultRedirect->setPath('sales/order/index');
            return $resultRedirect;
        }

        $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        if ($this->createGuaranteeAbility->isAvailable($orderId) && $this->creationService->createForOrder($orderId)) {
            $this->messageManager->addSuccessMessage(
                __('Order has been submitted for Guarantee.')
            );
        } else {
            $this->messageManager->addErrorMessage(
                __('Sorry, we cannot submit order for Guarantee.')
            );
        }

        return $resultRedirect;
    }
}
