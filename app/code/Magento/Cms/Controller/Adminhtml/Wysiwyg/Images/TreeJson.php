<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

class TreeJson extends \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images
{
    /**
     * Tree json action
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->_initAction();
            $this->getResponse()->representJson(
                $this->_view->getLayout()->createBlock(
                    'Magento\Cms\Block\Adminhtml\Wysiwyg\Images\Tree'
                )->getTreeJson()
            );
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
            );
        }
    }
}
