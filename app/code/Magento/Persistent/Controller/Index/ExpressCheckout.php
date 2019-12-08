<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Controller\Index;

use Magento\Persistent\Controller\Index;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * ExpressCheckout Controller
 *
 * @codeCoverageIgnore
 */
class ExpressCheckout extends Index implements HttpGetActionInterface
{
    /**
     * Add appropriate session message and redirect to shopping cart
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $this->messageManager->addNoticeMessage(__('Your shopping cart has been updated with new prices.'));
        /**
         * @var \Magento\Framework\Controller\Result\Redirect $resultRedirect
         */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('checkout/cart');
        return $resultRedirect;
    }
}
