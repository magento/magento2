<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types;

class Delete extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types
{
    /**
     * Delete attribute set mapping
     *
     * @return void
     */
    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            $model = $this->_objectManager->create('Magento\GoogleShopping\Model\Type');
            $model->load($id);
            if ($model->getTypeId()) {
                $model->delete();
            }
            $this->messageManager->addSuccess(__('Attribute set mapping was deleted'));
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $this->messageManager->addError(__("We can't delete Attribute Set Mapping."));
        }
        $this->_redirect('adminhtml/*/index', ['store' => $this->_getStore()->getId()]);
    }
}
