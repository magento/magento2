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
 * @package     Mage_Tag
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product tags admin controller
 *
 * @category   Mage
 * @package    Mage_Tag
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tag_Adminhtml_TagController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(
                Mage::helper('Mage_Tag_Helper_Data')->__('Catalog'), Mage::helper('Mage_Tag_Helper_Data')->__('Catalog')
            )
            ->_addBreadcrumb(
                Mage::helper('Mage_Tag_Helper_Data')->__('Tags'), Mage::helper('Mage_Tag_Helper_Data')->__('Tags')
            );

        return $this;
    }

    /**
     * Prepare tag model for manipulation
     *
     * @return Mage_Tag_Model_Tag | false
     */
    protected function _initTag()
    {
        $model = Mage::getModel('Mage_Tag_Model_Tag');
        $storeId = $this->getRequest()->getParam('store');
        $model->setStoreId($storeId);

        if (($id = $this->getRequest()->getParam('tag_id'))) {
            $model->setAddBasePopularity();
            $model->load($id);
            $model->setStoreId($storeId);

            if (!$model->getId()) {
                return false;
            }
        }

        Mage::register('current_tag', $model);
        return $model;
    }

    /**
     * Show grid action
     *
     */
    public function indexAction()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Tags'))
             ->_title($this->__('All Tags'));

        $this->_initAction()
            ->_addBreadcrumb(
                Mage::helper('Mage_Tag_Helper_Data')->__('All Tags'),
                Mage::helper('Mage_Tag_Helper_Data')->__('All Tags')
            )
            ->_setActiveMenu('Mage_Tag::catalog_tag_all')
            ->renderLayout();
    }

    /**
     * Action to draw grid loaded by ajax
     *
     */
    public function ajaxGridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Action to draw pending tags grid loaded by ajax
     *
     */
    public function ajaxPendingGridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * New tag action
     *
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit tag action
     *
     */
    public function editAction()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Tags'));

        if (! (int) $this->getRequest()->getParam('store')) {
            return $this->_redirect(
                '*/*/*/',
                array('store' => Mage::app()->getAnyStoreView()->getId(), '_current' => true)
            );
        }

        if (! ($model = $this->_initTag())) {
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(
                Mage::helper('Mage_Tag_Helper_Data')->__('Wrong tag was specified.')
            );
            return $this->_redirect('*/*/index', array('store' => $this->getRequest()->getParam('store')));
        }

        // set entered data if was error when we do save
        $data = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getTagData(true);
        if (! empty($data)) {
            $model->addData($data);
        }

        $this->_title($model->getId() ? $this->__('Edit Tag \'%s\'', $model->getName()) : $this->__('New Tag'));

        Mage::register('tag_tag', $model);

        $this->_initAction()->renderLayout();
    }

    /**
     * Save tag action
     *
     */
    public function saveAction()
    {
        if ($postData = $this->getRequest()->getPost()) {
            if (isset($postData['tag_id'])) {
                $data['tag_id'] = $postData['tag_id'];
            }

            $data['name']               = trim($postData['tag_name']);
            $data['status']             = $postData['tag_status'];
            $data['base_popularity']    = (isset($postData['base_popularity'])) ? $postData['base_popularity'] : 0;
            $data['store']              = $postData['store_id'];

            if (!$model = $this->_initTag()) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(
                    Mage::helper('Mage_Tag_Helper_Data')->__('Wrong tag was specified.')
                );
                return $this->_redirect('*/*/index', array('store' => $data['store']));
            }

            $model->addData($data);

            if (isset($postData['tag_assigned_products'])) {
                $productIds = Mage::helper('Mage_Adminhtml_Helper_Js')->decodeGridSerializedInput(
                    $postData['tag_assigned_products']
                );
                $tagRelationModel = Mage::getModel('Mage_Tag_Model_Tag_Relation');
                $tagRelationModel->addRelations($model, $productIds);
            }

            try {
                $model->save();

                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    Mage::helper('Mage_Tag_Helper_Data')->__('The tag has been saved.')
                );

                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setTagData(false);

                if (($continue = $this->getRequest()->getParam('continue'))) {
                    return $this->_redirect(
                        '*/tag/edit',
                        array('tag_id' => $model->getId(), 'store' => $model->getStoreId(), 'ret' => $continue)
                    );
                } else {
                    return $this->_redirect('*/tag/' . $this->getRequest()->getParam('ret', 'index'));
                }
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setTagData($data);

                return $this->_redirect(
                    '*/*/edit',
                    array('tag_id' => $model->getId(), 'store' => $model->getStoreId())
                );
            }
        }

        return $this->_redirect('*/tag/index', array('_current' => true));
    }

    /**
     * Delete tag action
     *
     * @return void
     */
    public function deleteAction()
    {
        $model   = $this->_initTag();
        $session = Mage::getSingleton('Mage_Adminhtml_Model_Session');

        if ($model && $model->getId()) {
            try {
                $model->delete();
                $session->addSuccess(Mage::helper('Mage_Tag_Helper_Data')->__('The tag has been deleted.'));
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        } else {
            $session->addError(Mage::helper('Mage_Tag_Helper_Data')->__('Unable to find a tag to delete.'));
        }

        $this->getResponse()->setRedirect($this->getUrl('*/tag/' . $this->getRequest()->getParam('ret', 'index')));
    }

    /**
     * Pending tags
     *
     */
    public function pendingAction()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Tags'))
             ->_title($this->__('Pending Tags'));

        $this->_initAction()
            ->_addBreadcrumb(
                Mage::helper('Mage_Tag_Helper_Data')->__('Pending Tags'),
                Mage::helper('Mage_Tag_Helper_Data')->__('Pending Tags')
            )
            ->renderLayout();
    }

    /**
     * Assigned products (with serializer block)
     *
     */
    public function assignedAction()
    {
        $this->_title($this->__('Tags'))->_title($this->__('Assigned'));

        $this->_initTag();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Assigned products grid
     *
     */
    public function assignedGridOnlyAction()
    {
        $this->_initTag();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Tagged products
     *
     */
    public function productAction()
    {
        $this->_initTag();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Customers
     *
     */
    public function customerAction()
    {
        $this->_initTag();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Massaction for removing tags
     *
     */
    public function massDeleteAction()
    {
        $tagIds = $this->getRequest()->getParam('tag');
        if (!is_array($tagIds)) {
             Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($this->__('Please select tag(s).'));
        } else {
            try {
                foreach ($tagIds as $tagId) {
                    $tag = Mage::getModel('Mage_Tag_Model_Tag')->load($tagId);
                    $tag->delete();
                }
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    $this->__('Total of %d record(s) have been deleted.', count($tagIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/' . $this->getRequest()->getParam('ret', 'index'));
    }

    /**
     * Massaction for changing status of selected tags
     *
     */
    public function massStatusAction()
    {
        $tagIds = $this->getRequest()->getParam('tag');
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        if (!is_array($tagIds)) {
            // No products selected
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($this->__('Please select tag(s).'));
        } else {
            try {
                foreach ($tagIds as $tagId) {
                    $tag = Mage::getModel('Mage_Tag_Model_Tag')
                        ->load($tagId)
                        ->setStatus($this->getRequest()->getParam('status'));
                     $tag->save();
                }
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    $this->__('Total of %d record(s) have been updated.', count($tagIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
            }
        }
        $ret = $this->getRequest()->getParam('ret') ? $this->getRequest()->getParam('ret') : 'index';
        $this->_redirect('*/*/'.$ret);
    }

    /**
     * Check currently called action by permissions for current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Tag::tag_all');
    }
}
