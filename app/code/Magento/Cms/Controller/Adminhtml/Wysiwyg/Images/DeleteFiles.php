<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Framework\App\Filesystem\DirectoryList;

class DeleteFiles extends \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images
{
    /**
     * Delete file from media storage
     *
     * @return void
     */
    public function execute()
    {
        try {
            if (!$this->getRequest()->isPost()) {
                throw new \Exception('Wrong request.');
            }
            $files = $this->getRequest()->getParam('files');

            /** @var $helper \Magento\Cms\Helper\Wysiwyg\Images */
            $helper = $this->_objectManager->get('Magento\Cms\Helper\Wysiwyg\Images');
            $path = $this->getStorage()->getSession()->getCurrentPath();
            foreach ($files as $file) {
                $file = $helper->idDecode($file);
                /** @var \Magento\Framework\Filesystem $filesystem */
                $filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
                $dir = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $filePath = $path . '/' . $file;
                if ($dir->isFile($dir->getRelativePath($filePath))) {
                    $this->getStorage()->deleteFile($filePath);
                }
            }
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
            );
        }
    }
}
