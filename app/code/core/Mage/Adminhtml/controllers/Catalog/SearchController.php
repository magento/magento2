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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Adminhtml_Catalog_SearchController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Mage_CatalogSearch::catalog_search')
            ->_addBreadcrumb(Mage::helper('Mage_Catalog_Helper_Data')->__('Search'), Mage::helper('Mage_Catalog_Helper_Data')->__('Search'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Catalog'))->_title($this->__('Search Terms'));

        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('Mage_Catalog_Helper_Data')->__('Catalog'), Mage::helper('Mage_Catalog_Helper_Data')->__('Catalog'))
            ->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Catalog_Search'))
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_title($this->__('Catalog'))->_title($this->__('Search Terms'));

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('Mage_CatalogSearch_Model_Query');

        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(Mage::helper('Mage_Catalog_Helper_Data')->__('This search no longer exists.'));
                $this->_redirect('*/*');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        Mage::register('current_catalog_search', $model);

        $this->_initAction();

        $this->_title($id ? $model->getQueryText() : $this->__('New Search'));

        $this->getLayout()->getBlock('head')->setCanLoadRulesJs(true);

        $this->getLayout()->getBlock('catalog_search_edit')
            ->setData('action', $this->getUrl('*/catalog_search/save'));

        $this
            ->_addBreadcrumb($id ? Mage::helper('Mage_Catalog_Helper_Data')->__('Edit Search') : Mage::helper('Mage_Catalog_Helper_Data')->__('New Search'), $id ? Mage::helper('Mage_Catalog_Helper_Data')->__('Edit Search') : Mage::helper('Mage_Catalog_Helper_Data')->__('New Search'));

        $this->renderLayout();
    }

    /**
     * Save search query
     *
     */
    public function saveAction()
    {
        $hasError   = false;
        $data       = $this->getRequest()->getPost();
        $queryId    = $this->getRequest()->getPost('query_id', null);
        if ($this->getRequest()->isPost() && $data) {
            /* @var $model Mage_CatalogSearch_Model_Query */
            $model = Mage::getModel('Mage_CatalogSearch_Model_Query');

            // validate query
            $queryText  = $this->getRequest()->getPost('query_text', false);
            $storeId    = $this->getRequest()->getPost('store_id', false);

            try {
                if ($queryText) {
                    $model->setStoreId($storeId);
                    $model->loadByQueryText($queryText);
                    if ($model->getId() && $model->getId() != $queryId) {
                        Mage::throwException(
                            Mage::helper('Mage_Catalog_Helper_Data')->__('Search Term with such search query already exists.')
                        );
                    } else if (!$model->getId() && $queryId) {
                        $model->load($queryId);
                    }
                } else if ($queryId) {
                    $model->load($queryId);
                }

                $model->addData($data);
                $model->setIsProcessed(0);
                $model->save();

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $hasError = true;
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('Mage_Catalog_Helper_Data')->__('An error occurred while saving the search query.')
                );
                $hasError = true;
            }
        }

        if ($hasError) {
            $this->_getSession()->setPageData($data);
            $this->_redirect('*/*/edit', array('id' => $queryId));
        } else {
            $this->_redirect('*/*');
        }
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('Mage_CatalogSearch_Model_Query');
                $model->setId($id);
                $model->delete();
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(Mage::helper('Mage_Catalog_Helper_Data')->__('The search was deleted.'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(Mage::helper('Mage_Catalog_Helper_Data')->__('Unable to find a search term to delete.'));
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $searchIds = $this->getRequest()->getParam('search');
        if(!is_array($searchIds)) {
             Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Please select catalog searches.'));
        } else {
            try {
                foreach ($searchIds as $searchId) {
                    $model = Mage::getModel('Mage_CatalogSearch_Model_Query')->load($searchId);
                    $model->delete();
                }
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('Total of %d record(s) were deleted', count($searchIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Backend_Model_Auth_Session')->isAllowed('Mage_CatalogSearch::search');
    }
}
