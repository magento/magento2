<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

class NewFolder extends \Magento\Cms\Controller\Adminhtml\Wysiwyg\Images
{
    /**
     * New folder action
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->_initAction();
            $name = $this->getRequest()->getPost('name');
            $path = $this->getStorage()->getSession()->getCurrentPath();
            $result = $this->getStorage()->createDirectory($name, $path);
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }
}
