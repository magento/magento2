<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Controller\Adminhtml\Guarantee;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Signifyd\Model\Guarantee\CreationService;

/**
 * Responsible for submitting case for Guarantee.
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
     * @param Context $context
     * @param CreationService $creationService
     */
    public function __construct(
        Context $context,
        CreationService $creationService
    ) {
        parent::__construct($context);
        $this->creationService = $creationService;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('orderId');
        $resultRedirect = $this->resultRedirectFactory->create();

        if (empty($orderId)) {
            $this->messageManager->addErrorMessage(__('Order id is required.'));
            $resultRedirect->setPath('sales/order/index');
            return $resultRedirect;
        }

        $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        if ($this->creationService->create($orderId)) {
            $this->messageManager->addSuccessMessage(
                __('Order has been submitted for Guarantee.')
            );
        } else {
            $this->messageManager->addErrorMessage(
                __('Sorry, we can\'t submit order for Guarantee.')
            );
        }

        return $resultRedirect;
    }
}
