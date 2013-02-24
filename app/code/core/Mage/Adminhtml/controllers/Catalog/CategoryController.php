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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog category controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Catalog_CategoryController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initialize requested category and put it into registry.
     * Root category can be returned, if inappropriate store/category is specified
     *
     * @param bool $getRootInstead
     * @return Mage_Catalog_Model_Category
     */
    protected function _initCategory($getRootInstead = false)
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Categories'))
             ->_title($this->__('Manage Categories'));

        $categoryId = (int) $this->getRequest()->getParam('id',false);
        $storeId    = (int) $this->getRequest()->getParam('store');
        $category = Mage::getModel('Mage_Catalog_Model_Category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = Mage::app()->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    // load root category instead wrong one
                    if ($getRootInstead) {
                        $category->load($rootId);
                    }
                    else {
                        $this->_redirect('*/*/', array('_current'=>true, 'id'=>null));
                        return false;
                    }
                }
            }
        }

        if ($activeTabId = (string) $this->getRequest()->getParam('active_tab_id')) {
            Mage::getSingleton('Mage_Backend_Model_Auth_Session')->setActiveTabId($activeTabId);
        }

        Mage::register('category', $category);
        Mage::register('current_category', $category);
        Mage::getSingleton('Mage_Cms_Model_Wysiwyg_Config')->setStoreId($this->getRequest()->getParam('store'));
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
        Mage::getSingleton('Mage_Backend_Model_Auth_Session')->unsActiveTabId();
        $this->_forward('edit');
    }

    /**
     * Edit category page
     */
    public function editAction()
    {
        $params['_current'] = true;
        $redirect = false;

        $storeId = (int) $this->getRequest()->getParam('store');
        $parentId = (int) $this->getRequest()->getParam('parent');
        $_prevStoreId = Mage::getSingleton('Mage_Backend_Model_Auth_Session')
            ->getLastViewedStore(true);

        if (!empty($_prevStoreId) && !$this->getRequest()->getQuery('isAjax')) {
            $params['store'] = $_prevStoreId;
            $redirect = true;
        }

        $categoryId = (int) $this->getRequest()->getParam('id');
        $_prevCategoryId = Mage::getSingleton('Mage_Backend_Model_Auth_Session')
            ->getLastEditedCategory(true);


        if ($_prevCategoryId
            && !$this->getRequest()->getQuery('isAjax')
            && !$this->getRequest()->getParam('clear')) {
           // $params['id'] = $_prevCategoryId;
             $this->getRequest()->setParam('id',$_prevCategoryId);
            //$redirect = true;
        }

         if ($redirect) {
            $this->_redirect('*/*/edit', $params);
            return;
        }

        if ($storeId && !$categoryId && !$parentId) {
            $store = Mage::app()->getStore($storeId);
            $_prevCategoryId = (int) $store->getRootCategoryId();
            $this->getRequest()->setParam('id', $_prevCategoryId);
        }

        if (!($category = $this->_initCategory(true))) {
            return;
        }

        $this->_title($categoryId ? $category->getName() : $this->__('New Category'));

        /**
         * Check if we have data in session (if duering category save was exceprion)
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
                $breadcrumbsPath = Mage::getSingleton('Mage_Backend_Model_Auth_Session')->getDeletedPath(true);
                if (!empty($breadcrumbsPath)) {
                    $breadcrumbsPath = explode('/', $breadcrumbsPath);
                    // no need to get parent breadcrumbs if deleting category level 1
                    if (count($breadcrumbsPath) <= 1) {
                        $breadcrumbsPath = '';
                    }
                    else {
                        array_pop($breadcrumbsPath);
                        $breadcrumbsPath = implode('/', $breadcrumbsPath);
                    }
                }
            }

            Mage::getSingleton('Mage_Backend_Model_Auth_Session')
                ->setLastViewedStore($this->getRequest()->getParam('store'));
            Mage::getSingleton('Mage_Backend_Model_Auth_Session')
                ->setLastEditedCategory($category->getId());
//            $this->_initLayoutMessages('Mage_Adminhtml_Model_Session');
            $this->loadLayout();

            $eventResponse = new Varien_Object(array(
                'content' => $this->getLayout()->getBlock('category.edit')->getFormHtml()
                    . $this->getLayout()->getBlock('category.tree')
                    ->getBreadcrumbsJavascript($breadcrumbsPath, 'editingCategoryBreadcrumbs'),
                'messages' => $this->getLayout()->getMessagesBlock()->getGroupedHtml(),
            ));

            Mage::dispatchEvent('category_prepare_ajax_response', array(
                'response' => $eventResponse,
                'controller' => $this
            ));

            $this->getResponse()->setBody(
                Mage::helper('Mage_Core_Helper_Data')->jsonEncode($eventResponse->getData())
            );

            return;
        }

        $this->loadLayout();
        $this->_setActiveMenu('Mage_Catalog::catalog_categories');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
            ->setContainerCssClass('catalog-categories');

        $this->_addBreadcrumb(Mage::helper('Mage_Catalog_Helper_Data')->__('Manage Catalog Categories'),
             Mage::helper('Mage_Catalog_Helper_Data')->__('Manage Categories')
        );

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
        $storeMediaUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $content = $this->getLayout()->createBlock(
            'Mage_Adminhtml_Block_Catalog_Helper_Form_Wysiwyg_Content',
            '',
            array(
                'editor_element_id' => $elementId,
                'store_id'          => $storeId,
                'store_media_url'   => $storeMediaUrl,
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
            Mage::getSingleton('Mage_Backend_Model_Auth_Session')->setIsTreeWasExpanded(true);
        } else {
            Mage::getSingleton('Mage_Backend_Model_Auth_Session')->setIsTreeWasExpanded(false);
        }
        if ($categoryId = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('Mage_Adminhtml_Block_Catalog_Category_Tree')
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
        if ($data = $this->getRequest()->getPost()) {
            $category->addData($data['general']);
            if (!$category->getId()) {
                $parentId = $this->getRequest()->getParam('parent');
                if (!$parentId) {
                    if ($storeId) {
                        $parentId = Mage::app()->getStore($storeId)->getRootCategoryId();
                    }
                    else {
                        $parentId = Mage_Catalog_Model_Category::TREE_ROOT_ID;
                    }
                }
                $parentCategory = Mage::getModel('Mage_Catalog_Model_Category')->load($parentId);
                $category->setPath($parentCategory->getPath());
            }

            /**
             * Process "Use Config Settings" checkboxes
             */
            if ($useConfig = $this->getRequest()->getPost('use_config')) {
                foreach ($useConfig as $attributeCode) {
                    $category->setData($attributeCode, null);
                }
            }

            /**
             * Create Permanent Redirect for old URL key
             */
            if ($category->getId() && isset($data['general']['url_key_create_redirect']))
            // && $category->getOrigData('url_key') != $category->getData('url_key')
            {
                $category->setData('save_rewrites_history', (bool)$data['general']['url_key_create_redirect']);
            }

            $category->setAttributeSetId($category->getDefaultAttributeSetId());

            if (isset($data['category_products']) &&
                !$category->getProductsReadonly()) {
                $products = array();
                parse_str($data['category_products'], $products);
                $category->setPostedProducts($products);
            }

            Mage::dispatchEvent('catalog_category_prepare_save', array(
                'category' => $category,
                'request' => $this->getRequest()
            ));

            /**
             * Check "Use Default Value" checkboxes values
             */
            if ($useDefaults = $this->getRequest()->getPost('use_default')) {
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
                            Mage::throwException(Mage::helper('Mage_Catalog_Helper_Data')->__('Attribute "%s" is required.', $category->getResource()->getAttribute($code)->getFrontend()->getLabel()));
                        }
                        else {
                            Mage::throwException($error);
                        }
                    }
                }

                $category->unsetData('use_post_data_config');
                if (isset($data['general']['entity_id'])) {
                    throw new Mage_Core_Exception($this->__('Unable to save the category'));
                }

                $category->save();
                $this->_getSession()->addSuccess(Mage::helper('Mage_Catalog_Helper_Data')->__('The category has been saved.'));
                $refreshTree = 'true';
            }
            catch (Exception $e){
                $this->_getSession()->addError($e->getMessage())
                    ->setCategoryData($data);
                $refreshTree = 'false';
            }
        }

        if ($this->getRequest()->getPost('return_session_messages_only')) {
            $category->load($category->getId()); // to obtain truncated category name

            /** @var $block Mage_Core_Block_Messages */
            $block = Mage::getSingleton('Mage_Core_Block_Messages');
            $block->setMessages($this->_getSession()->getMessages(true));
            $body = Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array(
                'messages' => $block->getGroupedHtml(),
                'error'    => $refreshTree !== 'true',
                'category' => $category->toArray(),
            ));
        } else {
            $url = $this->getUrl('*/*/edit', array('_current' => true, 'id' => $category->getId()));
            $body = '<script type="text/javascript">parent.updateContent("'
                . $url . '", {}, ' . $refreshTree
                . ');</script>';
        }

        $this->getResponse()->setBody($body);
    }

    /**
     * Move category action
     */
    public function moveAction()
    {
        $category = $this->_initCategory();
        if (!$category) {
            $this->getResponse()->setBody(Mage::helper('Mage_Catalog_Helper_Data')->__('Category move error'));
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
        }
        catch (Mage_Core_Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        }
        catch (Exception $e){
            $this->getResponse()->setBody(Mage::helper('Mage_Catalog_Helper_Data')->__('Category move error %s', $e));
            Mage::logException($e);
        }

    }

    /**
     * Delete category action
     */
    public function deleteAction()
    {
        if ($id = (int) $this->getRequest()->getParam('id')) {
            try {
                $category = Mage::getModel('Mage_Catalog_Model_Category')->load($id);
                Mage::dispatchEvent('catalog_controller_category_delete', array('category'=>$category));

                Mage::getSingleton('Mage_Backend_Model_Auth_Session')->setDeletedPath($category->getPath());

                $category->delete();
                $this->_getSession()->addSuccess(Mage::helper('Mage_Catalog_Helper_Data')->__('The category has been deleted.'));
            }
            catch (Mage_Core_Exception $e){
                $this->_getSession()->addError($e->getMessage());
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('_current'=>true)));
                return;
            }
            catch (Exception $e){
                $this->_getSession()->addError(Mage::helper('Mage_Catalog_Helper_Data')->__('An error occurred while trying to delete the category.'));
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('_current'=>true)));
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/', array('_current'=>true, 'id'=>null)));
    }

    /**
     * Grid Action
     * Display list of products related to current category
     *
     * @return void
     */
    public function gridAction()
    {
        if (!$category = $this->_initCategory(true)) {
            return;
        }
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('Mage_Adminhtml_Block_Catalog_Category_Tab_Product', 'category.product.grid')
                ->toHtml()
        );
    }

    /**
     * Tree Action
     * Retrieve category tree
     *
     * @return void
     */
    public function treeAction()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        $categoryId = (int) $this->getRequest()->getParam('id');

        if ($storeId) {
            if (!$categoryId) {
                $store = Mage::app()->getStore($storeId);
                $rootId = $store->getRootCategoryId();
                $this->getRequest()->setParam('id', $rootId);
            }
        }

        $category = $this->_initCategory(true);

        $block = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Catalog_Category_Tree');
        $root  = $block->getRoot();
        $this->getResponse()->setBody(Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array(
            'data' => $block->getTree(),
            'parameters' => array(
                'text'        => $block->buildNodeName($root),
                'draggable'   => false,
                'allowDrop'   => ($root->getIsVisible()) ? true : false,
                'id'          => (int) $root->getId(),
                'expanded'    => (int) $block->getIsWasExpanded(),
                'store_id'    => (int) $block->getStore()->getId(),
                'category_id' => (int) $category->getId(),
                'root_visible'=> (int) $root->getIsVisible()
        ))));
    }

    /**
    * Build response for refresh input element 'path' in form
    */
    public function refreshPathAction()
    {
        if ($id = (int) $this->getRequest()->getParam('id')) {
            $category = Mage::getModel('Mage_Catalog_Model_Category')->load($id);
            $this->getResponse()->setBody(
                Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array(
                   'id' => $id,
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
        $this->getResponse()->setBody($this->getLayout()->createBlock('Mage_Adminhtml_Block_Catalog_Category_Tree')
            ->getSuggestedCategoriesJson($this->getRequest()->getParam('label_part'))
        );
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Catalog::categories');
    }
}
