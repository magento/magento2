<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;
use Magento\Theme\Helper\Storage as ThemeStorageHelper;
use Psr\Log\LoggerInterface;

class PreviewImage extends Files
{
    /**
     * Preview image action
     *
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $file = $this->getRequest()->getParam('file');
        /** @var ThemeStorageHelper $helper */
        $helper = $this->_objectManager->get(ThemeStorageHelper::class);
        try {
            return $this->_fileFactory->create(
                $file,
                ['type' => 'filename', 'value' => $helper->getThumbnailPath($file)],
                DirectoryList::MEDIA
            );
        } catch (Exception $e) {
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
            $this->_redirect('core/index/notFound');
        }
    }
}
