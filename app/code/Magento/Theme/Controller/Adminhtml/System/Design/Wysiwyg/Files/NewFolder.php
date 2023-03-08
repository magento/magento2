<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;
use Psr\Log\LoggerInterface;

class NewFolder extends Files
{
    /**
     * New folder action
     *
     * @return void
     */
    public function execute()
    {
        $name = $this->getRequest()->getPost('name');
        try {
            $path = $this->storage->getCurrentPath();
            $result = $this->_getStorage()->createFolder($name, $path);
        } catch (LocalizedException $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
        } catch (Exception $e) {
            $result = ['error' => true, 'message' => __('Sorry, something went wrong. That\'s all we know.')];
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(JsonHelper::class)->jsonEncode($result)
        );
    }
}
