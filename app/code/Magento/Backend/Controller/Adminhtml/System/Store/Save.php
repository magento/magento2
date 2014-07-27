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

class Save extends \Magento\Backend\Controller\Adminhtml\System\Store
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->isPost() && ($postData = $this->getRequest()->getPost())) {
            if (empty($postData['store_type']) || empty($postData['store_action'])) {
                $this->_redirect('adminhtml/*/');
                return;
            }

            try {
                switch ($postData['store_type']) {
                    case 'website':
                        $postData['website']['name'] = $this->filterManager->removeTags($postData['website']['name']);
                        $websiteModel = $this->_objectManager->create('Magento\Store\Model\Website');
                        if ($postData['website']['website_id']) {
                            $websiteModel->load($postData['website']['website_id']);
                        }
                        $websiteModel->setData($postData['website']);
                        if ($postData['website']['website_id'] == '') {
                            $websiteModel->setId(null);
                        }

                        $websiteModel->save();
                        $this->messageManager->addSuccess(__('The website has been saved.'));
                        break;

                    case 'group':
                        $postData['group']['name'] = $this->filterManager->removeTags($postData['group']['name']);
                        $groupModel = $this->_objectManager->create('Magento\Store\Model\Group');
                        if ($postData['group']['group_id']) {
                            $groupModel->load($postData['group']['group_id']);
                        }
                        $groupModel->setData($postData['group']);
                        if ($postData['group']['group_id'] == '') {
                            $groupModel->setId(null);
                        }

                        $groupModel->save();

                        $this->_eventManager->dispatch('store_group_save', array('group' => $groupModel));

                        $this->messageManager->addSuccess(__('The store has been saved.'));
                        break;

                    case 'store':
                        $eventName = 'store_edit';
                        $storeModel = $this->_objectManager->create('Magento\Store\Model\Store');
                        $postData['store']['name'] = $this->filterManager->removeTags($postData['store']['name']);
                        if ($postData['store']['store_id']) {
                            $storeModel->load($postData['store']['store_id']);
                        }
                        $storeModel->setData($postData['store']);
                        if ($postData['store']['store_id'] == '') {
                            $storeModel->setId(null);
                            $eventName = 'store_add';
                        }
                        $groupModel = $this->_objectManager->create(
                            'Magento\Store\Model\Group'
                        )->load(
                            $storeModel->getGroupId()
                        );
                        $storeModel->setWebsiteId($groupModel->getWebsiteId());
                        $storeModel->save();

                        $this->_objectManager->get('Magento\Store\Model\StoreManager')->reinitStores();

                        $this->_eventManager->dispatch($eventName, array('store' => $storeModel));

                        $this->messageManager->addSuccess(__('The store view has been saved'));
                        break;
                    default:
                        $this->_redirect('adminhtml/*/');
                        return;
                }
                $this->_redirect('adminhtml/*/');
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_getSession()->setPostData($postData);
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('An error occurred while saving. Please review the error log.')
                );
                $this->_getSession()->setPostData($postData);
            }
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
            return;
        }
        $this->_redirect('adminhtml/*/');
    }
}
