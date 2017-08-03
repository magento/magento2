<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\Create\ProcessData
 *
 */
class ProcessData extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Process data and display index page
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $this->_initSession();
        $this->_processData();
        return $this->resultForwardFactory->create()->forward('index');
    }
}
