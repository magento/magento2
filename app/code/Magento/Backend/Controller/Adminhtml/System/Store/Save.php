<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;


class Save extends \Magento\Backend\Controller\Adminhtml\System\Store
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
        $redirectResult = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost() && ($postData = $this->getRequest()->getPostValue())) {
            if (empty($postData['store_type']) || empty($postData['store_action'])) {
                $redirectResult->setPath('adminhtml/*/');
                return $redirectResult;
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
                        $this->messageManager->addSuccess(__('You saved the website.'));
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

                        $this->_eventManager->dispatch('store_group_save', ['group' => $groupModel]);

                        $this->messageManager->addSuccess(__('You saved the store.'));
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

                        $this->_eventManager->dispatch($eventName, ['store' => $storeModel]);

                        $this->messageManager->addSuccess(__('You saved the store view.'));
                        break;
                    default:
                        $redirectResult->setPath('adminhtml/*/');
                        return $redirectResult;
                }
                $redirectResult->setPath('adminhtml/*/');
                return $redirectResult;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_getSession()->setPostData($postData);
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while saving. Please review the error log.')
                );
                $this->_getSession()->setPostData($postData);
            }
            $redirectResult->setUrl($this->_redirect->getRedirectUrl($this->getUrl('*')));
            return $redirectResult;
        }
        $redirectResult->setPath('adminhtml/*/');
        return $redirectResult;
    }
}
