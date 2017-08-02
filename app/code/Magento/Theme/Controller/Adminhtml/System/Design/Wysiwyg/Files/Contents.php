<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;

/**
 * Class \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files\Contents
 *
 * @since 2.0.0
 */
class Contents extends \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files
{
    /**
     * Contents action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        try {
            $this->_view->loadLayout('empty');
            $this->_view->getLayout()->getBlock('wysiwyg_files.files')->setStorage($this->_getStorage());
            $this->_view->renderLayout();

            $this->_getSession()->setStoragePath($this->storage->getCurrentPath());
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            $this->getResponse()->representJson(
                $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
            );
        }
    }
}
