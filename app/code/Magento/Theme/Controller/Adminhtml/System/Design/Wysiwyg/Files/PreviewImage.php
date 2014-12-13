<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $this->_redirect('core/index/notFound');
        }
    }
}
