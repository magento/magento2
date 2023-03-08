<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;

use Exception;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;

class Contents extends Files
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
            $this->_view->getLayout()->getBlock('wysiwyg_files.files')->setStorage($this->_getStorage());
            $this->_view->renderLayout();

            $this->_getSession()->setStoragePath($this->storage->getCurrentPath());
        } catch (Exception $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            $this->getResponse()->representJson(
                $this->_objectManager->get(JsonHelper::class)->jsonEncode($result)
            );
        }
    }
}
