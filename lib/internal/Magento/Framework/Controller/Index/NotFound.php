<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Controller\Index;

class NotFound extends \Magento\Framework\App\Action\Action
{
    /**
     * 404 not found action
     *
     * @return void
     */
    public function execute()
    {
        $this->getResponse()->setStatusHeader(404, '1.1', 'Not Found');
        $this->getResponse()->setBody((string)new \Magento\Framework\Phrase('Requested resource not found'));
    }
}
