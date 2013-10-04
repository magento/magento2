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

namespace Magento\Adminhtml\Controller\Catalog;

class Search extends \Magento\Adminhtml\Controller\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Controller\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\Controller\Context $context,
        \Magento\Core\Model\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Magento_CatalogSearch::catalog_search')
            ->_addBreadcrumb(__('Search'), __('Search'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_title(__('Search Terms'));

        $this->_initAction()
            ->_addBreadcrumb(__('Catalog'), __('Catalog'));
            $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_title(__('Search Terms'));

        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Magento\CatalogSearch\Model\Query');

        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError(__('This search no longer exists.'));
                $this->_redirect('*/*');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Adminhtml\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->_coreRegistry->register('current_catalog_search', $model);

        $this->_initAction();

        $this->_title($id ? $model->getQueryText() : __('New Search'));

        $this->getLayout()->getBlock('head')->setCanLoadRulesJs(true);

        $this->getLayout()->getBlock('adminhtml.catalog.search.edit')
            ->setData('action', $this->getUrl('*/catalog_search/save'));

        $this
            ->_addBreadcrumb($id ? __('Edit Search') : __('New Search'), $id ? __('Edit Search') : __('New Search'));

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
            /* @var $model \Magento\CatalogSearch\Model\Query */
            $model = $this->_objectManager->create('Magento\CatalogSearch\Model\Query');

            // validate query
            $queryText  = $this->getRequest()->getPost('query_text', false);
            $storeId    = $this->getRequest()->getPost('store_id', false);

            try {
                if ($queryText) {
                    $model->setStoreId($storeId);
                    $model->loadByQueryText($queryText);
                    if ($model->getId() && $model->getId() != $queryId) {
                        throw new \Magento\Core\Exception(
                            __('You already have an identical search term query.')
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

            } catch (\Magento\Core\Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $hasError = true;
            } catch (\Exception $e) {
                $this->_getSession()->addException($e,
                    __('Something went wrong while saving the search query.')
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
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create('Magento\CatalogSearch\Model\Query');
                $model->setId($id);
                $model->delete();
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addSuccess(__('You deleted the search.'));
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError(__('We can\'t find a search term to delete.'));
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $searchIds = $this->getRequest()->getParam('search');
        if (!is_array($searchIds)) {
             $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError(__('Please select catalog searches.'));
        } else {
            try {
                foreach ($searchIds as $searchId) {
                    $model = $this->_objectManager->create('Magento\CatalogSearch\Model\Query')->load($searchId);
                    $model->delete();
                }
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addSuccess(
                    __('Total of %1 record(s) were deleted', count($searchIds))
                );
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_CatalogSearch::search');
    }
}
