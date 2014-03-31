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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Controller\Adminhtml\System;

use Magento\Backend\App\Action;

class Design extends Action
{
    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Registry $coreRegistry
     * @param \Magento\Stdlib\DateTime\Filter\Date $dateFilter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Registry $coreRegistry,
        \Magento\Stdlib\DateTime\Filter\Date $dateFilter
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->dateFilter = $dateFilter;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Store Design'));
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Backend::system_design_schedule');
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function gridAction()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * @return void
     */
    public function editAction()
    {
        $this->_title->add(__('Store Design'));

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Backend::system_design_schedule');
        $this->_view->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $id = (int)$this->getRequest()->getParam('id');
        $design = $this->_objectManager->create('Magento\Core\Model\Design');

        if ($id) {
            $design->load($id);
        }

        $this->_title->add($design->getId() ? __('Edit Store Design Change') : __('New Store Design Change'));

        $this->_coreRegistry->register('design', $design);

        $this->_addContent($this->_view->getLayout()->createBlock('Magento\Backend\Block\System\Design\Edit'));
        $this->_addLeft(
            $this->_view->getLayout()->createBlock('Magento\Backend\Block\System\Design\Edit\Tabs', 'design_tabs')
        );

        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            $data['design'] = $this->_filterPostData($data['design']);
            $id = (int)$this->getRequest()->getParam('id');

            $design = $this->_objectManager->create('Magento\Core\Model\Design');
            if ($id) {
                $design->load($id);
            }

            $design->setData($data['design']);
            if ($id) {
                $design->setId($id);
            }
            try {
                $design->save();
                $this->_eventManager->dispatch('theme_save_after');
                $this->messageManager->addSuccess(__('You saved the design change.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_objectManager->get('Magento\Backend\Model\Session')->setDesignData($data);
                $this->_redirect('adminhtml/*/edit', array('id' => $design->getId()));
                return;
            }
        }

        $this->_redirect('adminhtml/*/');
    }

    /**
     * @return void
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $design = $this->_objectManager->create('Magento\Core\Model\Design')->load($id);

            try {
                $design->delete();
                $this->messageManager->addSuccess(__('You deleted the design change.'));
            } catch (\Magento\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __("Cannot delete the design change."));
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('adminhtml/*/'));
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Adminhtml::design');
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array $data
     * @return array|null
     */
    protected function _filterPostData($data)
    {
        $inputFilter = new \Zend_Filter_Input(
            array('date_from' => $this->dateFilter, 'date_to' => $this->dateFilter),
            array(),
            $data
        );
        $data = $inputFilter->getUnescaped();
        return $data;
    }
}
