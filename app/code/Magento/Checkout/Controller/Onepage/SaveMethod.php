<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Onepage;

class SaveMethod extends \Magento\Checkout\Controller\Onepage
{
    /**
     * Save checkout method
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost() || $this->_expireAjax()) {
            return $this->_ajaxRedirectResponse();
        }
        $method = $this->getRequest()->getPost('method');
        $result = $this->getOnepage()->saveCheckoutMethod($method);

        return $this->resultJsonFactory->create()->setData($result);
    }
}
