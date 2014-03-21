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
 * @package     Magento_Cms
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Cms\Controller\Adminhtml;

/**
 * Cms manage blocks controller
 *
 * @category   Magento
 * @package    Magento_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Block extends \Magento\Backend\App\Action
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
            'Magento_Cms::cms_block'
        )->_addBreadcrumb(
            __('CMS'),
            __('CMS')
        )->_addBreadcrumb(
            __('Static Blocks'),
            __('Static Blocks')
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
        $this->_title->add(__('Blocks'));

        $this->_initAction();
        $this->_view->renderLayout();
    }

    /**
     * Create new CMS block
     *
     * @return void
     */
    public function newAction()
    {
        // the same form is used to create and edit
        $this->_forward('edit');
    }

    /**
     * Edit CMS block
     *
     * @return void
     */
    public function editAction()
    {
        $this->_title->add(__('Blocks'));

        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('block_id');
        $model = $this->_objectManager->create('Magento\Cms\Model\Block');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This block no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title->add($model->getId() ? $model->getTitle() : __('New Block'));

        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('cms_block', $model);

        // 5. Build edit form
        $this->_initAction()->_addBreadcrumb(
            $id ? __('Edit Block') : __('New Block'),
            $id ? __('Edit Block') : __('New Block')
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
            $id = $this->getRequest()->getParam('block_id');
            $model = $this->_objectManager->create('Magento\Cms\Model\Block')->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This block no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }

            // init model and set data

            $model->setData($data);

            // try to save it
            try {
                // save the data
                $model->save();
                // display success message
                $this->messageManager->addSuccess(__('The block has been saved.'));
                // clear previously saved data from session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('block_id' => $model->getId()));
                    return;
                }
                // go to grid
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // save data in session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                // redirect to edit form
                $this->_redirect('*/*/edit', array('block_id' => $this->getRequest()->getParam('block_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete action
     *
     * @return void
     */
    public function deleteAction()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('block_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create('Magento\Cms\Model\Block');
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccess(__('The block has been deleted.'));
                // go to grid
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $this->_redirect('*/*/edit', array('block_id' => $id));
                return;
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a block to delete.'));
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
        return $this->_authorization->isAllowed('Magento_Cms::block');
    }
}
