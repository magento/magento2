<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;


class LoadBlock extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Loading page block
     *
     * @return void
     */
    public function execute()
    {
        $request = $this->getRequest();
        try {
            $this->_initSession()->_processData();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_reloadQuote();
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_reloadQuote();
            $this->messageManager->addException($e, $e->getMessage());
        }

        $asJson = $request->getParam('json');
        $block = $request->getParam('block');

        $update = $this->_view->getLayout()->getUpdate();
        if ($asJson) {
            $update->addHandle('sales_order_create_load_block_json');
        } else {
            $update->addHandle('sales_order_create_load_block_plain');
        }

        if ($block) {
            $blocks = explode(',', $block);
            if ($asJson && !in_array('message', $blocks)) {
                $blocks[] = 'message';
            }

            foreach ($blocks as $block) {
                $update->addHandle('sales_order_create_load_block_' . $block);
            }
        }
        $this->_view->loadLayoutUpdates();
        $this->_view->generateLayoutXml();
        $this->_view->generateLayoutBlocks();
        $result = $this->_view->getLayout()->renderElement('content');
        if ($request->getParam('as_js_varname')) {
            $this->_objectManager->get('Magento\Backend\Model\Session')->setUpdateResult($result);
            $this->_redirect('sales/*/showUpdateResult');
        } else {
            $this->getResponse()->setBody($result);
        }
    }
}
