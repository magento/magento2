<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;

use Exception;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Tree;
use Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;
use Psr\Log\LoggerInterface;

class TreeJson extends Files
{
    /**
     * Tree json action
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->getResponse()->representJson(
                $this->_view->getLayout()->createBlock(
                    Tree::class
                )->getTreeJson(
                    $this->_getStorage()->getTreeArray()
                )
            );
        } catch (Exception $e) {
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
            $this->getResponse()->representJson(
                $this->_objectManager->get(JsonHelper::class)->jsonEncode([])
            );
        }
    }
}
