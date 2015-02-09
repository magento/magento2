<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor\Files;

class Contents extends \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files
{
    /**
     * Contents action
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->_view->loadLayout('empty');
            $this->_view->getLayout()->getBlock('editor_files.files')->setStorage($this->_getStorage());
            $this->_view->renderLayout();

            $this->_getSession()->setStoragePath(
                $this->_objectManager->get('Magento\Theme\Helper\Storage')->getCurrentPath()
            );
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
            );
        }
    }
}
