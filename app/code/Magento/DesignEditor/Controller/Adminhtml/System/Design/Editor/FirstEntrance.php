<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
