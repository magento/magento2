<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Guest;

use Magento\Framework\App\Action;

class View extends \Magento\Sales\Controller\AbstractController\View
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $result = $this->orderLoader->load($this->_request);
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }

        $resultPage = $this->resultPageFactory->create();
        $this->_objectManager->get('Magento\Sales\Helper\Guest')->getBreadcrumbs($resultPage);
        return $resultPage;
    }
}
