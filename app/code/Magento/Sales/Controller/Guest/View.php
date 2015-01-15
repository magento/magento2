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
        if (!$this->orderLoader->load($this->_request, $this->_response)) {
            return;
        }

        $resultPage = $this->resultPageFactory->create();
        $this->_objectManager->get('Magento\Sales\Helper\Guest')->getBreadcrumbs($resultPage);
        return $resultPage;
    }
}
