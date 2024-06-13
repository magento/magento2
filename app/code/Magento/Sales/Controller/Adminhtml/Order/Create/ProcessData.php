<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

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
