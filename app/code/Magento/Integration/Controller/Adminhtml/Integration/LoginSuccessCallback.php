<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Integration\Controller\Adminhtml\Integration;

class LoginSuccessCallback extends \Magento\Integration\Controller\Adminhtml\Integration
{
    /**
     * Close window after callback has succeeded
     *
     * @return void
     */
    public function execute()
    {
        $this->getResponse()->setBody('<script>setTimeout(self.close.bind(this), 1000);</script>');
    }
}
