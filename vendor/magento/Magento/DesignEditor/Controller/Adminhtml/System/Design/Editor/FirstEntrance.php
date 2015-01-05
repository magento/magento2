<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor;

class FirstEntrance extends \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor
{
    /**
     * Display available theme list. Only when no customized themes
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->_resolveForwarding()) {
            $this->_renderStoreDesigner();
        }
    }
}
