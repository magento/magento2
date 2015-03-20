<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(\Magento\Framework\App\Action\Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        return $this->resultRedirectFactory->create()->setPath('checkout/onepage', ['_secure' => true]);
    }
}
