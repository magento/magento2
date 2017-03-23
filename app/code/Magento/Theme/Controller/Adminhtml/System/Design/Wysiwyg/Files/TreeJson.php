<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files;

class TreeJson extends \Magento\Theme\Controller\Adminhtml\System\Design\Wysiwyg\Files
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
                    \Magento\Theme\Block\Adminhtml\Wysiwyg\Files\Tree::class
                )->getTreeJson(
                    $this->_getStorage()->getTreeArray()
                )
            );
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->getResponse()->representJson(
                $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode([])
            );
        }
    }
}
