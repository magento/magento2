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
namespace Magento\Backend\Controller\Adminhtml\System\Store;

class EditStore extends \Magento\Backend\Controller\Adminhtml\System\Store
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_title->add(__('Stores'));

        if ($this->_getSession()->getPostData()) {
            $this->_coreRegistry->register('store_post_data', $this->_getSession()->getPostData());
            $this->_getSession()->unsPostData();
        }
        if (!$this->_coreRegistry->registry('store_type')) {
            $this->_coreRegistry->register('store_type', 'store');
        }
        if (!$this->_coreRegistry->registry('store_action')) {
            $this->_coreRegistry->register('store_action', 'edit');
        }
        switch ($this->_coreRegistry->registry('store_type')) {
            case 'website':
                $itemId = $this->getRequest()->getParam('website_id', null);
                $model = $this->_objectManager->create('Magento\Store\Model\Website');
                $title = __("Web Site");
                $notExists = __("The website does not exist.");
                $codeBase = __('Before modifying the website code please make sure that it is not used in index.php.');
                break;
            case 'group':
                $itemId = $this->getRequest()->getParam('group_id', null);
                $model = $this->_objectManager->create('Magento\Store\Model\Group');
                $title = __("Store");
                $notExists = __("The store does not exist");
                $codeBase = false;
                break;
            case 'store':
                $itemId = $this->getRequest()->getParam('store_id', null);
                $model = $this->_objectManager->create('Magento\Store\Model\Store');
                $title = __("Store View");
                $notExists = __("Store view doesn't exist");
                $codeBase = __(
                    'Before modifying the store view code please make sure that it is not used in index.php.'
                );
                break;
            default:
                break;
        }
        if (null !== $itemId) {
            $model->load($itemId);
        }

        if ($model->getId() || $this->_coreRegistry->registry('store_action') == 'add') {
            $this->_coreRegistry->register('store_data', $model);

            if ($this->_coreRegistry->registry('store_action') == 'add') {
                $this->_title->add(__('New ') . $title);
            } else {
                $this->_title->add($model->getName());
            }

            if ($this->_coreRegistry->registry('store_action') == 'edit' && $codeBase && !$model->isReadOnly()) {
                $this->messageManager->addNotice($codeBase);
            }

            $this->_initAction()->_addContent(
                $this->_view->getLayout()->createBlock('Magento\Backend\Block\System\Store\Edit')
            );
            $this->_view->renderLayout();
        } else {
            $this->messageManager->addError($notExists);
            $this->_redirect('adminhtml/*/');
        }
    }
}
