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


/**
 * Google sitemap controller
 *
 * @category   Mage
 * @package    Mage_Sitemap
 */
class Mage_Adminhtml_SitemapController extends  Mage_Adminhtml_Controller_Action
{
    /**
     * Init actions
     *
     * @return Mage_Adminhtml_SitemapController
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('Mage_Sitemap::catalog_sitemap')
            ->_addBreadcrumb(
                Mage::helper('Mage_Catalog_Helper_Data')->__('Catalog'),
                Mage::helper('Mage_Catalog_Helper_Data')->__('Catalog'))
            ->_addBreadcrumb(
                Mage::helper('Mage_Sitemap_Helper_Data')->__('Google Sitemap'),
                Mage::helper('Mage_Sitemap_Helper_Data')->__('Google Sitemap'))
        ;
        return $this;
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_title($this->__('Catalog'))->_title($this->__('Google Sitemaps'));

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Sitemap'))
            ->renderLayout();
    }

    /**
     * Create new sitemap
     */
    public function newAction()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }

    /**
     * Edit sitemap
     */
    public function editAction()
    {
        $this->_title($this->__('Catalog'))->_title($this->__('Google Sitemaps'));

        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('sitemap_id');
        $model = Mage::getModel('Mage_Sitemap_Model_Sitemap');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(
                    Mage::helper('Mage_Sitemap_Helper_Data')->__('This sitemap no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getSitemapFilename() : $this->__('New Sitemap'));

        // 3. Set entered data if was error when we do save
        $data = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getFormData(true);
        if (! empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        Mage::register('sitemap_sitemap', $model);

        // 5. Build edit form
        $this->_initAction()
            ->_addBreadcrumb(
                $id ? Mage::helper('Mage_Sitemap_Helper_Data')->__('Edit Sitemap') : Mage::helper('Mage_Sitemap_Helper_Data')->__('New Sitemap'),
                $id ? Mage::helper('Mage_Sitemap_Helper_Data')->__('Edit Sitemap') : Mage::helper('Mage_Sitemap_Helper_Data')->__('New Sitemap'))
            ->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Sitemap_Edit'))
            ->renderLayout();
    }

    /**
     * Save action
     */
    public function saveAction()
    {
        // check if data sent
        if ($data = $this->getRequest()->getPost()) {
            // init model and set data
            $model = Mage::getModel('Mage_Sitemap_Model_Sitemap');

            //validate path to generate
            if (!empty($data['sitemap_filename']) && !empty($data['sitemap_path'])) {
                $path = rtrim($data['sitemap_path'], '\\/')
                      . DS . $data['sitemap_filename'];
                /** @var $validator Mage_Core_Model_File_Validator_AvailablePath */
                $validator = Mage::getModel('Mage_Core_Model_File_Validator_AvailablePath');
                /** @var $helper Mage_Adminhtml_Helper_Catalog */
                $helper = Mage::helper('Mage_Adminhtml_Helper_Catalog');
                $validator->setPaths($helper->getSitemapValidPaths());
                if (!$validator->isValid($path)) {
                    foreach ($validator->getMessages() as $message) {
                        Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($message);
                    }
                    // save data in session
                    Mage::getSingleton('Mage_Adminhtml_Model_Session')->setFormData($data);
                    // redirect to edit form
                    $this->_redirect('*/*/edit', array(
                        'sitemap_id' => $this->getRequest()->getParam('sitemap_id')));
                    return;
                }
            }

            if ($this->getRequest()->getParam('sitemap_id')) {
                $model ->load($this->getRequest()->getParam('sitemap_id'));

                if ($model->getSitemapFilename() && file_exists($model->getPreparedFilename())){
                    unlink($model->getPreparedFilename());
                }
            }

            $model->setData($data);

            // try to save it
            try {
                // save the data
                $model->save();
                // display success message
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    Mage::helper('Mage_Sitemap_Helper_Data')->__('The sitemap has been saved.'));
                // clear previously saved data from session
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('sitemap_id' => $model->getId()));
                    return;
                }
                // go to grid or forward to generate action
                if ($this->getRequest()->getParam('generate')) {
                    $this->getRequest()->setParam('sitemap_id', $model->getId());
                    $this->_forward('generate');
                    return;
                }
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
                // save data in session
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setFormData($data);
                // redirect to edit form
                $this->_redirect('*/*/edit', array(
                    'sitemap_id' => $this->getRequest()->getParam('sitemap_id')));
                return;
            }
        }
        $this->_redirect('*/*/');

    }

    /**
     * Delete action
     */
    public function deleteAction()
    {
        // check if we know what should be deleted
        if ($id = $this->getRequest()->getParam('sitemap_id')) {
            try {
                // init model and delete
                $model = Mage::getModel('Mage_Sitemap_Model_Sitemap');
                $model->setId($id);
                // init and load sitemap model

                /* @var $sitemap Mage_Sitemap_Model_Sitemap */
                $model->load($id);
                // delete file
                if ($model->getSitemapFilename() && file_exists($model->getPreparedFilename())){
                    unlink($model->getPreparedFilename());
                }
                $model->delete();
                // display success message
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    Mage::helper('Mage_Sitemap_Helper_Data')->__('The sitemap has been deleted.'));
                // go to grid
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
                // go back to edit form
                $this->_redirect('*/*/edit', array('sitemap_id' => $id));
                return;
            }
        }
        // display error message
        Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(
            Mage::helper('Mage_Sitemap_Helper_Data')->__('Unable to find a sitemap to delete.'));
        // go to grid
        $this->_redirect('*/*/');
    }

    /**
     * Generate sitemap
     */
    public function generateAction()
    {
        // init and load sitemap model
        $id = $this->getRequest()->getParam('sitemap_id');
        $sitemap = Mage::getModel('Mage_Sitemap_Model_Sitemap');
        /* @var $sitemap Mage_Sitemap_Model_Sitemap */
        $sitemap->load($id);
        // if sitemap record exists
        if ($sitemap->getId()) {
            try {
                $sitemap->generateXml();

                $this->_getSession()->addSuccess(
                    Mage::helper('Mage_Sitemap_Helper_Data')->__('The sitemap "%s" has been generated.', $sitemap->getSitemapFilename()));
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('Mage_Sitemap_Helper_Data')->__('Unable to generate the sitemap.'));
            }
        } else {
            $this->_getSession()->addError(
                Mage::helper('Mage_Sitemap_Helper_Data')->__('Unable to find a sitemap to generate.'));
        }

        // go to grid
        $this->_redirect('*/*/');
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Backend_Model_Auth_Session')->isAllowed('catalog/sitemap');
    }
}
