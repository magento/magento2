<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class PreviewImage extends \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files
{
    /**
     * Preview image action
     *
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $file = $this->getRequest()->getParam('file');
        /** @var $helper \Magento\Theme\Helper\Storage */
        $helper = $this->_objectManager->get('Magento\Theme\Helper\Storage');
        try {
            return $this->_fileFactory->create(
                $file,
                ['type' => 'filename', 'value' => $helper->getThumbnailPath($file)],
                DirectoryList::MEDIA
            );
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->_redirect('core/index/notFound');
        }
    }
}
