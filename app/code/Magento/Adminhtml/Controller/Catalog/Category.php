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
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog category controller
 */
namespace Magento\Adminhtml\Controller\Catalog;

class Category extends \Magento\Adminhtml\Controller\Action
{
    /**
     * Initialize requested category and put it into registry.
     * Root category can be returned, if inappropriate store/category is specified
     *
     * @param bool $getRootInstead
     * @return \Magento\Catalog\Model\Category
     */
    protected function _initCategory($getRootInstead = false)
    {
        $this->_title(__('Categories'));

        $categoryId = (int)$this->getRequest()->getParam('id', false);
        $storeId    = (int)$this->getRequest()->getParam('store');
        $category = $this->_objectManager->create('Magento\Catalog\Model\Category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore($storeId)
                    ->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    // load root category instead wrong one
                    if ($getRootInstead) {
                        $category->load($rootId);
                    } else {
                        $this->_redirect('*/*/', array('_current'=>true, 'id'=>null));
                        return false;
                    }
                }
            }
        }

        $activeTabId = (string)$this->getRequest()->getParam('active_tab_id');
        if ($activeTabId) {
            $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->setActiveTabId($activeTabId);
        }
        $this->_objectManager->get('Magento\Core\Model\Registry')->register('category', $category);
        $this->_objectManager->get('Magento\Core\Model\Registry')->register('current_category', $category);
        $this->_objectManager->get('Magento\Cms\Model\Wysiwyg\Config')->setStoreId($this->getRequest()->getParam('store'));
        return $category;
    }
    /**
     * Catalog categories index action
     */
    public function indexAction()
    {
        $this->_forward('edit');
    }

    /**
     * Add new category form
     */
    public function addAction()
    {
        $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->unsActiveTabId();
        $this->_forward('edit');
    }

    /**
     * Edit category page
     */
    public function editAction()
    {
        $params['_current'] = true;
        $redirect = false;

        $storeId = (int)$this->getRequest()->getParam('store');
        $parentId = (int)$this->getRequest()->getParam('parent');
        $prevStoreId = $this->_objectManager->get('Magento\Backend\Model\Auth\Session')
            ->getLastViewedStore(true);

        if (!empty($prevStoreId) && !$this->getRequest()->getQuery('isAjax')) {
            $params['store'] = $prevStoreId;
            $redirect = true;
        }

        $categoryId = (int)$this->getRequest()->getParam('id');
        $_prevCategoryId = $this->_objectManager->get('Magento\Backend\Model\Auth\Session')
            ->getLastEditedCategory(true);

        if ($_prevCategoryId
            && !$this->getRequest()->getQuery('isAjax')
            && !$this->getRequest()->getParam('clear')
        ) {
             $this->getRequest()->setParam('id', $_prevCategoryId);
        }

        if ($redirect) {
            $this->_redirect('*/*/edit', $params);
            return;
        }

        if ($storeId && !$categoryId && !$parentId) {
            $store = $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore($storeId);
            $_prevCategoryId = (int) $store->getRootCategoryId();
            $this->getRequest()->setParam('id', $_prevCategoryId);
        }

        $category = $this->_initCategory(true);
        if (!$category) {
            return;
        }

        $this->_title($categoryId ? $category->getName() : __('Categories'));

        /**
         * Check if we have data in session (if during category save was exception)
         */
        $data = $this->_getSession()->getCategoryData(true);
        if (isset($data['general'])) {
            $category->addData($data['general']);
        }

        /**
         * Build response for ajax request
         */
        if ($this->getRequest()->getQuery('isAjax')) {
            // prepare breadcrumbs of selected category, if any
            $breadcrumbsPath = $category->getPath();
            if (empty($breadcrumbsPath)) {
                // but if no category, and it is deleted - prepare breadcrumbs from path, saved in session
                $breadcrumbsPath = $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->getDeletedPath(true);
                if (!empty($breadcrumbsPath)) {
                    $breadcrumbsPath = explode('/', $breadcrumbsPath);
                    // no need to get parent breadcrumbs if deleting category level 1
                    if (count($breadcrumbsPath) <= 1) {
                        $breadcrumbsPath = '';
                    } else {
                        array_pop($breadcrumbsPath);
                        $breadcrumbsPath = implode('/', $breadcrumbsPath);
                    }
                }
            }

            $this->_objectManager->get('Magento\Backend\Model\Auth\Session')
                ->setLastViewedStore($this->getRequest()->getParam('store'));
            $this->_objectManager->get('Magento\Backend\Model\Auth\Session')
                ->setLastEditedCategory($category->getId());
            $this->loadLayout();

            $eventResponse = new \Magento\Object(array(
                'content' => $this->getLayout()->getBlock('category.edit')->getFormHtml()
                    . $this->getLayout()->getBlock('category.tree')
                        ->getBreadcrumbsJavascript($breadcrumbsPath, 'editingCategoryBreadcrumbs'),
                'messages' => $this->getLayout()->getMessagesBlock()->getGroupedHtml(),
            ));
            $this->_eventManager->dispatch(
                'category_prepare_ajax_response',
                array(
                    'response' => $eventResponse,
                    'controller' => $this
            ));
            $this->getResponse()->setHeader('Content-type', 'application/json', true);
            $this->getResponse()->setBody(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($eventResponse->getData())
            );
            return;
        }

        $this->loadLayout();
        $this->_setActiveMenu('Magento_Catalog::catalog_categories');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)->setContainerCssClass('catalog-categories');

        $this->_addBreadcrumb(__('Manage Catalog Categories'), __('Manage Categories'));

        $block = $this->getLayout()->getBlock('catalog.wysiwyg.js');
        if ($block) {
            $block->setStoreId($storeId);
        }

        $this->renderLayout();
    }

    /**
     * WYSIWYG editor action for ajax request
     *
     */
    public function wysiwygAction()
    {
        $elementId = $this->getRequest()->getParam('element_id', md5(microtime()));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore($storeId)
            ->getBaseUrl(\Magento\Core\Model\Store::URL_TYPE_MEDIA);

        $content = $this->getLayout()->createBlock(
            'Magento\Adminhtml\Block\Catalog\Helper\Form\Wysiwyg\Content',
            '',
            array(
                'data' => array(
                    'editor_element_id' => $elementId,
                    'store_id'          => $storeId,
                    'store_media_url'   => $storeMediaUrl,
                )
            )
        );

        $this->getResponse()->setBody($content->toHtml());
    }

    /**
     * Get tree node (Ajax version)
     */
    public function categoriesJsonAction()
    {
        if ($this->getRequest()->getParam('expand_all')) {
            $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->setIsTreeWasExpanded(true);
        } else {
            $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->setIsTreeWasExpanded(false);
        }
        $categoryId = (int)$this->getRequest()->getPost('id');
        if ($categoryId) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('Magento\Adminhtml\Block\Catalog\Category\Tree')
                    ->getTreeJson($category)
            );
        }
    }

    /**
     * Category save
     */
    public function saveAction()
    {
        if (!$category = $this->_initCategory()) {
            return;
        }

        $storeId = $this->getRequest()->getParam('store');
        $refreshTree = 'false';
        $data = $this->getRequest()->getPost();
        if ($data) {
            $category->addData($this->_filterCategoryPostData($data['general']));
            if (!$category->getId()) {
                $parentId = $this->getRequest()->getParam('parent');
                if (!$parentId) {
                    if ($storeId) {
                        $parentId = $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')
                            ->getStore($storeId)->getRootCategoryId();
                    } else {
                        $parentId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
                    }
                }
                $parentCategory = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($parentId);
                $category->setPath($parentCategory->getPath());
            }

            /**
             * Process "Use Config Settings" checkboxes
             */
            $useConfig = $this->getRequest()->getPost('use_config');
            if ($useConfig) {
                foreach ($useConfig as $attributeCode) {
                    $category->setData($attributeCode, null);
                }
            }

            /**
             * Create Permanent Redirect for old URL key
             */
            // && $category->getOrigData('url_key') != $category->getData('url_key')
            if ($category->getId() && isset($data['general']['url_key_create_redirect'])) {
                $category->setData('save_rewrites_history', (bool)$data['general']['url_key_create_redirect']);
            }

            $category->setAttributeSetId($category->getDefaultAttributeSetId());

            if (isset($data['category_products']) && !$category->getProductsReadonly()) {
                $products = array();
                parse_str($data['category_products'], $products);
                $category->setPostedProducts($products);
            }
            $this->_eventManager->dispatch(
                'catalog_category_prepare_save',
                array(
                    'category' => $category,
                    'request' => $this->getRequest()
            ));

            /**
             * Check "Use Default Value" checkboxes values
             */
            $useDefaults = $this->getRequest()->getPost('use_default');
            if ($useDefaults) {
                foreach ($useDefaults as $attributeCode) {
                    $category->setData($attributeCode, false);
                }
            }

            /**
             * Proceed with $_POST['use_config']
             * set into category model for processing through validation
             */
            $category->setData('use_post_data_config', $this->getRequest()->getPost('use_config'));

            try {
                $validate = $category->validate();
                if ($validate !== true) {
                    foreach ($validate as $code => $error) {
                        if ($error === true) {
                            $attribute = $category->getResource()->getAttribute($code)->getFrontend()->getLabel();
                            throw new \Magento\Core\Exception(
                                __('Attribute "%1" is required.', $attribute)
                            );
                        } else {
                            throw new \Magento\Core\Exception($error);
                        }
                    }
                }

                $category->unsetData('use_post_data_config');
                if (isset($data['general']['entity_id'])) {
                    throw new \Magento\Core\Exception(__('Unable to save the category'));
                }

                $category->save();
                $this->_getSession()->addSuccess(__('You saved the category.'));
                $refreshTree = 'true';
            } catch (\Exception $e){
                $this->_getSession()->addError($e->getMessage())->setCategoryData($data);
                $refreshTree = 'false';
            }
        }

        if ($this->getRequest()->getPost('return_session_messages_only')) {
            $category->load($category->getId()); // to obtain truncated category name

            /** @var $block \Magento\Core\Block\Messages */
            $block = $this->_objectManager->get('Magento\Core\Block\Messages');
            $block->setMessages($this->_getSession()->getMessages(true));
            $body = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(array(
                'messages' => $block->getGroupedHtml(),
                'error'    => $refreshTree !== 'true',
                'category' => $category->toArray(),
            ));
        } else {
            $url = $this->getUrl('*/*/edit', array('_current' => true, 'id' => $category->getId()));
            $body = '<script type="text/javascript">parent.updateContent("'
                . $url . '", {}, ' . $refreshTree . ');</script>';
        }

        $this->getResponse()->setBody($body);
    }

    /**
     * Filter category data
     *
     * @param array $rawData
     * @return array
     */
    protected function _filterCategoryPostData(array $rawData)
    {
        $data = $rawData;
        // @todo It is a workaround to prevent saving this data in category model and it has to be refactored in future
        if (isset($data['image']) && is_array($data['image'])) {
            $data['image_additional_data'] = $data['image'];
            unset($data['image']);
        }
        return $data;
    }

    /**
     * Move category action
     */
    public function moveAction()
    {
        $category = $this->_initCategory();
        if (!$category) {
            $this->getResponse()->setBody(__('There was a category move error.'));
            return;
        }
        /**
         * New parent category identifier
         */
        $parentNodeId   = $this->getRequest()->getPost('pid', false);
        /**
         * Category id after which we have put our category
         */
        $prevNodeId     = $this->getRequest()->getPost('aid', false);

        try {
            $category->move($parentNodeId, $prevNodeId);
            $this->getResponse()->setBody('SUCCESS');
        } catch (\Magento\Core\Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        } catch (\Exception $e){
            $this->getResponse()->setBody(__('There was a category move error %1', $e));
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
        }

    }

    /**
     * Delete category action
     */
    public function deleteAction()
    {
        $categoryId = (int)$this->getRequest()->getParam('id');
        if ($categoryId) {
            try {
                $category = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($categoryId);
                $this->_eventManager->dispatch(
                    'catalog_controller_category_delete', array('category' => $category)
                );

                $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->setDeletedPath($category->getPath());

                $category->delete();
                $this->_getSession()->addSuccess(__('You deleted the category.'));
            } catch (\Magento\Core\Exception $e){
                $this->_getSession()->addError($e->getMessage());
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('_current' => true)));
                return;
            } catch (\Exception $e){
                $this->_getSession()->addError(__('Something went wrong while trying to delete the category.'));
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('_current' => true)));
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/', array('_current' => true, 'id' => null)));
    }

    /**
     * Grid Action
     * Display list of products related to current category
     */
    public function gridAction()
    {
        $category = $this->_initCategory(true);
        if (!$category) {
            return;
        }
        $this->getResponse()->setBody($this->getLayout()->createBlock(
            'Magento\Adminhtml\Block\Catalog\Category\Tab\Product', 'category.product.grid'
        )->toHtml());
    }

    /**
     * Tree Action
     * Retrieve category tree
     */
    public function treeAction()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        $categoryId = (int)$this->getRequest()->getParam('id');

        if ($storeId) {
            if (!$categoryId) {
                $store = $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore($storeId);
                $rootId = $store->getRootCategoryId();
                $this->getRequest()->setParam('id', $rootId);
            }
        }

        $category = $this->_initCategory(true);

        $block = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Catalog\Category\Tree');
        $root  = $block->getRoot();
        $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(array(
            'data' => $block->getTree(),
            'parameters' => array(
                'text'        => $block->buildNodeName($root),
                'draggable'   => false,
                'allowDrop'   => (bool)$root->getIsVisible(),
                'id'          => (int)$root->getId(),
                'expanded'    => (int)$block->getIsWasExpanded(),
                'store_id'    => (int)$block->getStore()->getId(),
                'category_id' => (int)$category->getId(),
                'root_visible'=> (int)$root->getIsVisible()
        ))));
    }

    /**
     * Build response for refresh input element 'path' in form
     */
    public function refreshPathAction()
    {
        $categoryId = (int)$this->getRequest()->getParam('id');
        if ($categoryId) {
            $category = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($categoryId);
            $this->getResponse()->setBody(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(array(
                   'id' => $categoryId,
                   'path' => $category->getPath(),
                ))
            );
        }
    }

    /**
     * Category list suggestion based on already entered symbols
     */
    public function suggestCategoriesAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('Magento\Adminhtml\Block\Catalog\Category\Tree')
            ->getSuggestedCategoriesJson($this->getRequest()->getParam('label_part')));
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::categories');
    }
}
