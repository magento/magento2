<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor\Files;

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
                    'Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Files\Tree'
                )->getTreeJson(
                    $this->_getStorage()->getTreeArray()
                )
            );
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode([])
            );
        }
    }
}
