<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;

/**
 * Class \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files\Upload
 *
 * @since 2.0.0
 */
class Upload extends \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files
{
    /**
     * Files upload action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        try {
            $path = $this->storage->getCurrentPath();
            $result = $this->_getStorage()->uploadFile($path);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }
}
