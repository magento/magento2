<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Controller\Index;

class NotFound extends \Magento\Framework\App\Action\Action
{
    /**
     * 404 not found action
     *
     * @return void
     */
    public function execute()
    {
        $this->getResponse()->setHeader('HTTP/1.1', '404 Not Found');
        $this->getResponse()->setHttpResponseCode(404);
        $this->getResponse()->setBody(__('Requested resource not found'));
    }
}
