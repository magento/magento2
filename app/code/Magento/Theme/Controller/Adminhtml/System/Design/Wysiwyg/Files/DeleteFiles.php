<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;

/**
 * Class \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files\DeleteFiles
 *
 * @since 2.0.0
 */
class DeleteFiles extends \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files
{
    /**
     * Delete file from media storage
     *
     * @return void
     * @throws \Exception
     * @since 2.0.0
     */
    public function execute()
    {
        try {
            if (!$this->getRequest()->isPost()) {
                throw new \Exception('Wrong request');
            }
            $files = $this->_objectManager->get(
                \Magento\Framework\Json\Helper\Data::class
            )->jsonDecode(
                $this->getRequest()->getParam('files')
            );
            foreach ($files as $file) {
                $this->_getStorage()->deleteFile($file);
            }
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            $this->getResponse()->representJson(
                $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
            );
        }
    }
}
