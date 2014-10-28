<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->_redirect('adminhtml/*/index', array('store' => $this->_getStore()->getId()));
    }
}
