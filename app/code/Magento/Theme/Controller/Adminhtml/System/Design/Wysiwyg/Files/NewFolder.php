<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;

class NewFolder extends \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files
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
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => __('Sorry, something went wrong. That\'s all we know.')];
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }
}
