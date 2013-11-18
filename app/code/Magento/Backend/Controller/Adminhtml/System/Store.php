<?php
/**
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
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Store controller
 *
 * @category    Magento
 * @package     Magento_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Controller\Adminhtml\System;

class Store extends \Magento\Backend\Controller\Adminhtml\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @param \Magento\Backend\Controller\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\Filter\FilterManager $filterManager
     */
    public function __construct(
        \Magento\Backend\Controller\Context $context,
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\Filter\FilterManager $filterManager
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->filterManager = $filterManager;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Cms\Controller\Adminhtml\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('Magento_Adminhtml::system_store')
            ->_addBreadcrumb(__('System'), __('System'))
            ->_addBreadcrumb(__('Manage Stores'), __('Manage Stores'));
        return $this;
    }

    public function indexAction()
    {
        $this->_title(__('Stores'));
        $this->_initAction()
            ->renderLayout();
    }

    public function newWebsiteAction()
    {
        $this->_coreRegistry->register('store_type', 'website');
        $this->_forward('newStore');
    }

    public function newGroupAction()
    {
        $this->_coreRegistry->register('store_type', 'group');
        $this->_forward('newStore');
    }

    public function newStoreAction()
    {
        if (!$this->_coreRegistry->registry('store_type')) {
            $this->_coreRegistry->register('store_type', 'store');
        }
        $this->_coreRegistry->register('store_action', 'add');
        $this->_forward('editStore');
    }

    public function editWebsiteAction()
    {
        $this->_coreRegistry->register('store_type', 'website');
        $this->_forward('editStore');
    }

    public function editGroupAction()
    {
        $this->_coreRegistry->register('store_type', 'group');
        $this->_forward('editStore');
    }

    public function editStoreAction()
    {
        $this->_title(__('Stores'));

        $session = $this->_getSession();
        if ($session->getPostData()) {
            $this->_coreRegistry->register('store_post_data', $session->getPostData());
            $session->unsPostData();
        }
        if (!$this->_coreRegistry->registry('store_type')) {
            $this->_coreRegistry->register('store_type', 'store');
        }
        if (!$this->_coreRegistry->registry('store_action')) {
            $this->_coreRegistry->register('store_action', 'edit');
        }
        switch ($this->_coreRegistry->registry('store_type')) {
            case 'website':
                $itemId     = $this->getRequest()->getParam('website_id', null);
                $model      = $this->_objectManager->create('Magento\Core\Model\Website');
                $title      = __("Web Site");
                $notExists  = __("The website does not exist.");
                $codeBase   = __('Before modifying the website code please make sure that it is not used in index.php.');
                break;
            case 'group':
                $itemId     = $this->getRequest()->getParam('group_id', null);
                $model      = $this->_objectManager->create('Magento\Core\Model\Store\Group');
                $title      = __("Store");
                $notExists  = __("The store does not exist");
                $codeBase   = false;
                break;
            case 'store':
                $itemId     = $this->getRequest()->getParam('store_id', null);
                $model      = $this->_objectManager->create('Magento\Core\Model\Store');
                $title      = __("Store View");
                $notExists  = __("Store view doesn't exist");
                $codeBase   = __('Before modifying the store view code please make sure that it is not used in index.php.');
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
                $this->_title(__('New ') . $title);
            } else {
                $this->_title($model->getName());
            }

            if ($this->_coreRegistry->registry('store_action') == 'edit' && $codeBase && !$model->isReadOnly()) {
                $this->_getSession()->addNotice($codeBase);
            }

            $this->_initAction()
                ->_addContent($this->getLayout()->createBlock('Magento\Backend\Block\System\Store\Edit'))
                ->renderLayout();
        } else {
            $session->addError($notExists);
            $this->_redirect('adminhtml/*/');
        }
    }

    public function saveAction()
    {
        if ($this->getRequest()->isPost() && $postData = $this->getRequest()->getPost()) {
            if (empty($postData['store_type']) || empty($postData['store_action'])) {
                $this->_redirect('adminhtml/*/');
                return;
            }
            $session = $this->_getSession();

            try {
                switch ($postData['store_type']) {
                    case 'website':
                        $postData['website']['name'] = $this->filterManager->removeTags($postData['website']['name']);
                        $websiteModel = $this->_objectManager->create('Magento\Core\Model\Website');
                        if ($postData['website']['website_id']) {
                            $websiteModel->load($postData['website']['website_id']);
                        }
                        $websiteModel->setData($postData['website']);
                        if ($postData['website']['website_id'] == '') {
                            $websiteModel->setId(null);
                        }

                        $websiteModel->save();
                        $session->addSuccess(__('The website has been saved.'));
                        break;

                    case 'group':
                        $postData['group']['name'] = $this->filterManager->removeTags($postData['group']['name']);
                        $groupModel = $this->_objectManager->create('Magento\Core\Model\Store\Group');
                        if ($postData['group']['group_id']) {
                            $groupModel->load($postData['group']['group_id']);
                        }
                        $groupModel->setData($postData['group']);
                        if ($postData['group']['group_id'] == '') {
                            $groupModel->setId(null);
                        }

                        $groupModel->save();

                        $this->_eventManager->dispatch('store_group_save', array('group' => $groupModel));

                        $session->addSuccess(__('The store has been saved.'));
                        break;

                    case 'store':
                        $eventName = 'store_edit';
                        $storeModel = $this->_objectManager->create('Magento\Core\Model\Store');
                        $postData['store']['name'] = $this->filterManager->removeTags($postData['store']['name']);
                        if ($postData['store']['store_id']) {
                            $storeModel->load($postData['store']['store_id']);
                        }
                        $storeModel->setData($postData['store']);
                        if ($postData['store']['store_id'] == '') {
                            $storeModel->setId(null);
                            $eventName = 'store_add';
                        }
                        $groupModel = $this->_objectManager->create('Magento\Core\Model\Store\Group')
                            ->load($storeModel->getGroupId());
                        $storeModel->setWebsiteId($groupModel->getWebsiteId());
                        $storeModel->save();

                        $this->_objectManager->get('Magento\Core\Model\StoreManager')->reinitStores();

                        $this->_eventManager->dispatch($eventName, array('store'=>$storeModel));

                        $session->addSuccess(__('The store view has been saved'));
                        break;
                    default:
                        $this->_redirect('adminhtml/*/');
                        return;
                }
                $this->_redirect('adminhtml/*/');
                return;
            } catch (\Magento\Core\Exception $e) {
                $this->_getSession()->addMessages($e->getMessages());
                $session->setPostData($postData);
            } catch (\Exception $e) {
                $session->addException($e, __('An error occurred while saving. Please review the error log.'));
                $session->setPostData($postData);
            }
            $this->_redirectReferer();
            return;
        }
        $this->_redirect('adminhtml/*/');
    }

    public function deleteWebsiteAction()
    {
        $this->_title(__('Delete Web Site'));

        $session = $this->_getSession();
        $itemId = $this->getRequest()->getParam('item_id', null);
        if (!$model = $this->_objectManager->create('Magento\Core\Model\Website')->load($itemId)) {
            $session->addError(__('Unable to proceed. Please, try again.'));
            $this->_redirect('adminhtml/*/');
            return ;
        }
        if (!$model->isCanDelete()) {
            $session->addError(__('This website cannot be deleted.'));
            $this->_redirect('adminhtml/*/editWebsite', array('website_id' => $itemId));
            return ;
        }

        $this->_addDeletionNotice('website');

        $this->_initAction()
            ->_addBreadcrumb(__('Delete Web Site'), __('Delete Web Site'))
            ->_addContent($this->getLayout()->createBlock('Magento\Backend\Block\System\Store\Delete')
                ->setFormActionUrl($this->getUrl('adminhtml/*/deleteWebsitePost'))
                ->setBackUrl($this->getUrl('adminhtml/*/editWebsite', array('website_id' => $itemId)))
                ->setStoreTypeTitle(__('Web Site'))
                ->setDataObject($model)
            )
            ->renderLayout();
    }

    public function deleteGroupAction()
    {
        $this->_title(__('Delete Store'));

        $session = $this->_getSession();
        $itemId = $this->getRequest()->getParam('item_id', null);
        if (!$model = $this->_objectManager->create('Magento\Core\Model\Store\Group')->load($itemId)) {
            $session->addError(__('Unable to proceed. Please, try again.'));
            $this->_redirect('adminhtml/*/');
            return ;
        }
        if (!$model->isCanDelete()) {
            $session->addError(__('This store cannot be deleted.'));
            $this->_redirect('adminhtml/*/editGroup', array('group_id' => $itemId));
            return ;
        }

        $this->_addDeletionNotice('store');

        $this->_initAction()
            ->_addBreadcrumb(__('Delete Store'), __('Delete Store'))
            ->_addContent($this->getLayout()->createBlock('Magento\Backend\Block\System\Store\Delete')
                ->setFormActionUrl($this->getUrl('adminhtml/*/deleteGroupPost'))
                ->setBackUrl($this->getUrl('adminhtml/*/editGroup', array('group_id' => $itemId)))
                ->setStoreTypeTitle(__('Store'))
                ->setDataObject($model)
            )
            ->renderLayout();
    }

    public function deleteStoreAction()
    {
        $this->_title(__('Delete Store View'));

        $session = $this->_getSession();
        $itemId = $this->getRequest()->getParam('item_id', null);
        if (!$model = $this->_objectManager->create('Magento\Core\Model\Store')->load($itemId)) {
            $session->addError(__('Unable to proceed. Please, try again.'));
            $this->_redirect('adminhtml/*/');
            return ;
        }
        if (!$model->isCanDelete()) {
            $session->addError(__('This store view cannot be deleted.'));
            $this->_redirect('adminhtml/*/editStore', array('store_id' => $itemId));
            return ;
        }

        $this->_addDeletionNotice('store view');;

        $this->_initAction()
            ->_addBreadcrumb(__('Delete Store View'), __('Delete Store View'))
            ->_addContent($this->getLayout()->createBlock('Magento\Backend\Block\System\Store\Delete')
                ->setFormActionUrl($this->getUrl('adminhtml/*/deleteStorePost'))
                ->setBackUrl($this->getUrl('adminhtml/*/editStore', array('store_id' => $itemId)))
                ->setStoreTypeTitle(__('Store View'))
                ->setDataObject($model)
            )
            ->renderLayout();
    }

    public function deleteWebsitePostAction()
    {
        $itemId = $this->getRequest()->getParam('item_id');
        $model = $this->_objectManager->create('Magento\Core\Model\Website')->load($itemId);

        if (!$model) {
            $this->_getSession()->addError(__('Unable to proceed. Please, try again'));
            $this->_redirect('adminhtml/*/');
            return ;
        }
        if (!$model->isCanDelete()) {
            $this->_getSession()->addError(__('This website cannot be deleted.'));
            $this->_redirect('adminhtml/*/editWebsite', array('website_id' => $model->getId()));
            return ;
        }

        $this->_backupDatabase('*/*/editWebsite', array('website_id' => $itemId));

        try {
            $model->delete();
            $this->_getSession()->addSuccess(__('The website has been deleted.'));
            $this->_redirect('adminhtml/*/');
            return ;
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('Unable to delete website. Please, try again later.'));
        }
        $this->_redirect('adminhtml/*/editWebsite', array('website_id' => $itemId));
    }

    public function deleteGroupPostAction()
    {
        $itemId = $this->getRequest()->getParam('item_id');

        if (!$model = $this->_objectManager->create('Magento\Core\Model\Store\Group')->load($itemId)) {
            $this->_getSession()->addError(__('Unable to proceed. Please, try again.'));
            $this->_redirect('adminhtml/*/');
            return ;
        }
        if (!$model->isCanDelete()) {
            $this->_getSession()->addError(__('This store cannot be deleted.'));
            $this->_redirect('adminhtml/*/editGroup', array('group_id' => $model->getId()));
            return ;
        }

        $this->_backupDatabase('*/*/editGroup', array('group_id' => $itemId));

        try {
            $model->delete();
            $this->_getSession()->addSuccess(__('The store has been deleted.'));
            $this->_redirect('adminhtml/*/');
            return ;
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('Unable to delete store. Please, try again later.'));
        }
        $this->_redirect('adminhtml/*/editGroup', array('group_id' => $itemId));
    }

    /**
     * Delete store view post action
     *
     */
    public function deleteStorePostAction()
    {
        $itemId = $this->getRequest()->getParam('item_id');

        if (!$model = $this->_objectManager->create('Magento\Core\Model\Store')->load($itemId)) {
            $this->_getSession()->addError(__('Unable to proceed. Please, try again'));
            $this->_redirect('adminhtml/*/');
            return ;
        }
        if (!$model->isCanDelete()) {
            $this->_getSession()->addError(__('This store view cannot be deleted.'));
            $this->_redirect('adminhtml/*/editStore', array('store_id' => $model->getId()));
            return ;
        }

        $this->_backupDatabase('*/*/editStore', array('store_id' => $itemId));

        try {
            $model->delete();

            $this->_eventManager->dispatch('store_delete', array('store' => $model));

            $this->_getSession()->addSuccess(__('The store view has been deleted.'));
            $this->_redirect('adminhtml/*/');
            return ;
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('Unable to delete store view. Please, try again later.'));
        }
        $this->_redirect('adminhtml/*/editStore', array('store_id' => $itemId));
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Adminhtml::store');
    }

    /**
     * Backup database
     *
     * @param string $failPath redirect path if backup failed
     * @param array $arguments
     * @return \Magento\Backend\Controller\Adminhtml\System\Store
     */
    protected function _backupDatabase($failPath, $arguments=array())
    {
        if (! $this->getRequest()->getParam('create_backup')) {
            return $this;
        }
        try {
            $backupDb = $this->_objectManager->create('Magento\Backup\Model\Db');
            $backup   = $this->_objectManager->create('Magento\Backup\Model\Backup')
                ->setTime(time())
                ->setType('db')
                ->setPath($this->_objectManager->get('Magento\App\Dir')->getDir('var') . DS . 'backups');

            $backupDb->createBackup($backup);
            $this->_getSession()->addSuccess(__('The database was backed up.'));
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect($failPath, $arguments);
            return ;
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('We couldn\'t create a backup right now. Please try again later.'));
            $this->_redirect($failPath, $arguments);
            return ;
        }
        return $this;
    }

    /**
     * Add notification on deleting store / store view / website
     *
     * @param string $typeTitle
     * @return \Magento\Backend\Controller\Adminhtml\System\Store
     */
    protected function _addDeletionNotice($typeTitle)
    {
        $this->_getSession()->addNotice(
            __('Deleting a %1 will not delete the information associated with the %1 (e.g. categories, products, etc.), but the %1 will not be able to be restored. It is suggested that you create a database backup before deleting the %1.', $typeTitle)
        );
        return $this;
    }

}
