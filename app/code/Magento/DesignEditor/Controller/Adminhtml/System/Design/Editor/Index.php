<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor;

class Index extends \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor
{
    /**
     * Display the design editor launcher page
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
