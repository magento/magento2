<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Controller\Adminhtml\Integration;

/**
 * Class \Magento\Integration\Controller\Adminhtml\Integration\LoginSuccessCallback
 *
 * @since 2.0.0
 */
class LoginSuccessCallback extends \Magento\Integration\Controller\Adminhtml\Integration
{
    /**
     * Close window after callback has succeeded
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->getResponse()->setBody('<script>setTimeout("self.close()",1000);</script>');
    }
}
