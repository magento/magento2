<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

class Contents extends \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images
{
    /**
     * Save current path in session
     *
     * @return $this
     */
    protected function _saveSessionCurrentPath()
    {
        $this->getStorage()->getSession()->setCurrentPath(
            $this->_objectManager->get('Magento\Cms\Helper\Wysiwyg\Images')->getCurrentPath()
        );
        return $this;
    }

    /**
     * Contents action
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->_initAction()->_saveSessionCurrentPath();
            $this->_view->loadLayout('empty');
            $this->_view->renderLayout();
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
            );
        }
    }
}
