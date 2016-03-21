<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Controller\Index;

use Magento\Persistent\Controller\Index;
use Magento\Framework\Controller\ResultFactory;

/**
 * @codeCoverageIgnore
 */
class ExpressCheckout extends Index
{
    /**
     * Add appropriate session message and redirect to shopping cart
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $this->messageManager->addNotice(__('Your shopping cart has been updated with new prices.'));
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('checkout/cart');
        return $resultRedirect;
    }
}
