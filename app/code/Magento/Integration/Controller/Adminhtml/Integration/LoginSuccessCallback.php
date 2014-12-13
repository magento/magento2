<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
        $this->getResponse()->setBody('<script type="text/javascript">setTimeout("self.close()",1000);</script>');
    }
}
