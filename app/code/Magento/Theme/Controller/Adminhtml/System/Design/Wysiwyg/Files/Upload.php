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

class Upload extends Files
{
    /**
     * Files upload action
     *
     * @return void
     */
    public function execute()
    {
        try {
            $path = $this->storage->getCurrentPath();
            $result = $this->_getStorage()->uploadFile($path);
        } catch (Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(JsonHelper::class)->jsonEncode($result)
        );
    }
}
