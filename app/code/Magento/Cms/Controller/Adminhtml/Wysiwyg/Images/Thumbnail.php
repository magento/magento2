<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

class Thumbnail extends \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images
{
    /**
     * Generate image thumbnail on the fly
     *
     * @return void
     */
    public function execute()
    {
        $file = $this->getRequest()->getParam('file');
        $file = $this->_objectManager->get('Magento\Cms\Helper\Wysiwyg\Images')->idDecode($file);
        $thumb = $this->getStorage()->resizeOnTheFly($file);
        if ($thumb !== false) {
            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $image */
            $image = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
            $image->open($thumb);
            $this->getResponse()->setHeader('Content-Type', $image->getMimeType())->setBody($image->getImage());
        } else {
            // todo: generate some placeholder
        }
    }
}
