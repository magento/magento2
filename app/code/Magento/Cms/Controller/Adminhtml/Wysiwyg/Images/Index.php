<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

class Index extends \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $storeId = (int)$this->getRequest()->getParam('store');

        try {
            $this->_objectManager->get('Magento\Cms\Helper\Wysiwyg\Images')->getCurrentPath();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_initAction();
        $this->_view->loadLayout('overlay_popup');
        $block = $this->_view->getLayout()->getBlock('wysiwyg_images.js');
        if ($block) {
            $block->setStoreId($storeId);
        }
        $this->_view->renderLayout();
    }
}
