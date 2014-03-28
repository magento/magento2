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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sitemap\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * XML sitemap controller
 */
class Sitemap extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Registry $coreRegistry
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return $this
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_Sitemap::catalog_sitemap'
        )->_addBreadcrumb(
            __('Catalog'),
            __('Catalog')
        )->_addBreadcrumb(
            __('XML Sitemap'),
            __('XML Sitemap')
        );
        return $this;
    }

    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Site Map'));
        $this->_initAction();
        $this->_view->renderLayout();
    }

    /**
     * Create new sitemap
     *
     * @return void
     */
    public function newAction()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }

    /**
     * Edit sitemap
     *
     * @return void
     */
    public function editAction()
    {
        $this->_title->add(__('Site Map'));

        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('sitemap_id');
        $model = $this->_objectManager->create('Magento\Sitemap\Model\Sitemap');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This sitemap no longer exists.'));
                $this->_redirect('adminhtml/*/');
                return;
            }
        }

        $this->_title->add($model->getId() ? $model->getSitemapFilename() : __('New Site Map'));

        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('sitemap_sitemap', $model);

        // 5. Build edit form
        $this->_initAction()->_addBreadcrumb(
            $id ? __('Edit Sitemap') : __('New Sitemap'),
            $id ? __('Edit Sitemap') : __('New Sitemap')
        )->_addContent(
            $this->_view->getLayout()->createBlock('Magento\Sitemap\Block\Adminhtml\Edit')
        );
        $this->_view->renderLayout();
    }

    /**
     * Save action
     *
     * @return void
     */
    public function saveAction()
    {
        // check if data sent
        $data = $this->getRequest()->getPost();
        if ($data) {
            // init model and set data
            /** @var \Magento\Sitemap\Model\Sitemap $model */
            $model = $this->_objectManager->create('Magento\Sitemap\Model\Sitemap');

            //validate path to generate
            if (!empty($data['sitemap_filename']) && !empty($data['sitemap_path'])) {
                $path = rtrim($data['sitemap_path'], '\\/') . '/' . $data['sitemap_filename'];
                /** @var $validator \Magento\Core\Model\File\Validator\AvailablePath */
                $validator = $this->_objectManager->create('Magento\Core\Model\File\Validator\AvailablePath');
                /** @var $helper \Magento\Catalog\Helper\Catalog */
                $helper = $this->_objectManager->get('Magento\Catalog\Helper\Catalog');
                $validator->setPaths($helper->getSitemapValidPaths());
                if (!$validator->isValid($path)) {
                    foreach ($validator->getMessages() as $message) {
                        $this->messageManager->addError($message);
                    }
                    // save data in session
                    $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                    // redirect to edit form
                    $this->_redirect(
                        'adminhtml/*/edit',
                        array('sitemap_id' => $this->getRequest()->getParam('sitemap_id'))
                    );
                    return;
                }
            }

            /** @var \Magento\Filesystem\Directory\Write $directory */
            $directory = $this->_objectManager->get(
                'Magento\App\Filesystem'
            )->getDirectoryWrite(
                \Magento\App\Filesystem::ROOT_DIR
            );

            if ($this->getRequest()->getParam('sitemap_id')) {
                $model->load($this->getRequest()->getParam('sitemap_id'));
                $fileName = $model->getSitemapFilename();

                $path = $model->getSitemapPath() . '/' . $fileName;
                if ($fileName && $directory->isFile($path)) {
                    $directory->delete($path);
                }
            }

            $model->setData($data);

            // try to save it
            try {
                // save the data
                $model->save();
                // display success message
                $this->messageManager->addSuccess(__('The sitemap has been saved.'));
                // clear previously saved data from session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('adminhtml/*/edit', array('sitemap_id' => $model->getId()));
                    return;
                }
                // go to grid or forward to generate action
                if ($this->getRequest()->getParam('generate')) {
                    $this->getRequest()->setParam('sitemap_id', $model->getId());
                    $this->_forward('generate');
                    return;
                }
                $this->_redirect('adminhtml/*/');
                return;
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // save data in session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                // redirect to edit form
                $this->_redirect(
                    'adminhtml/*/edit',
                    array('sitemap_id' => $this->getRequest()->getParam('sitemap_id'))
                );
                return;
            }
        }
        $this->_redirect('adminhtml/*/');
    }

    /**
     * Delete action
     *
     * @return void
     */
    public function deleteAction()
    {
        /** @var \Magento\Filesystem\Directory\Write $directory */
        $directory = $this->_objectManager->get(
            'Magento\App\Filesystem'
        )->getDirectoryWrite(
            \Magento\App\Filesystem::ROOT_DIR
        );

        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('sitemap_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create('Magento\Sitemap\Model\Sitemap');
                $model->setId($id);
                // init and load sitemap model

                /* @var $sitemap \Magento\Sitemap\Model\Sitemap */
                $model->load($id);
                // delete file
                $path = $directory->getRelativePath($model->getPreparedFilename());
                if ($model->getSitemapFilename() && $directory->isFile($path)) {
                    $directory->delete($path);
                }
                $model->delete();
                // display success message
                $this->messageManager->addSuccess(__('The sitemap has been deleted.'));
                // go to grid
                $this->_redirect('adminhtml/*/');
                return;
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $this->_redirect('adminhtml/*/edit', array('sitemap_id' => $id));
                return;
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a sitemap to delete.'));
        // go to grid
        $this->_redirect('adminhtml/*/');
    }

    /**
     * Generate sitemap
     *
     * @return void
     */
    public function generateAction()
    {
        // init and load sitemap model
        $id = $this->getRequest()->getParam('sitemap_id');
        $sitemap = $this->_objectManager->create('Magento\Sitemap\Model\Sitemap');
        /* @var $sitemap \Magento\Sitemap\Model\Sitemap */
        $sitemap->load($id);
        // if sitemap record exists
        if ($sitemap->getId()) {
            try {
                $sitemap->generateXml();

                $this->messageManager->addSuccess(
                    __('The sitemap "%1" has been generated.', $sitemap->getSitemapFilename())
                );
            } catch (\Magento\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong generating the sitemap.'));
            }
        } else {
            $this->messageManager->addError(__('We can\'t find a sitemap to generate.'));
        }

        // go to grid
        $this->_redirect('adminhtml/*/');
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sitemap::sitemap');
    }
}
