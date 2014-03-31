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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

/**
 * Order status management controller
 *
 * @category    Magento
 * @package     Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Status extends \Magento\Backend\App\Action
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
     * Initialize status model based on status code in request
     *
     * @return \Magento\Sales\Model\Order\Status|false
     */
    protected function _initStatus()
    {
        $statusCode = $this->getRequest()->getParam('status');
        if ($statusCode) {
            $status = $this->_objectManager->create('Magento\Sales\Model\Order\Status')->load($statusCode);
        } else {
            $status = false;
        }
        return $status;
    }

    /**
     * Statuses grid page
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Order Status'));
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Sales::system_order_statuses');
        $this->_view->renderLayout();
    }

    /**
     * New status form
     *
     * @return void
     */
    public function newAction()
    {
        $data = $this->_getSession()->getFormData(true);
        if ($data) {
            $status = $this->_objectManager->create('Magento\Sales\Model\Order\Status')->setData($data);
            $this->_coreRegistry->register('current_status', $status);
        }
        $this->_title->add(__('Order Status'));
        $this->_title->add(__('Create New Order Status'));
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Sales::system_order_statuses');
        $this->_view->renderLayout();
    }

    /**
     * Editing existing status form
     *
     * @return void
     */
    public function editAction()
    {
        $status = $this->_initStatus();
        if ($status) {
            $this->_coreRegistry->register('current_status', $status);
            $this->_title->add(__('Order Status'));
            $this->_title->add(__('Edit Order Status'));
            $this->_view->loadLayout();
            $this->_setActiveMenu('Magento_Sales::system_order_statuses');
            $this->_view->renderLayout();
        } else {
            $this->messageManager->addError(__('We can\'t find this order status.'));
            $this->_redirect('sales/');
        }
    }

    /**
     * Save status form processing
     *
     * @return void
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        $isNew = $this->getRequest()->getParam('is_new');
        if ($data) {

            $statusCode = $this->getRequest()->getParam('status');

            //filter tags in labels/status
            /** @var $filterManager \Magento\Filter\FilterManager */
            $filterManager = $this->_objectManager->get('Magento\Filter\FilterManager');
            if ($isNew) {
                $statusCode = $data['status'] = $filterManager->stripTags($data['status']);
            }
            $data['label'] = $filterManager->stripTags($data['label']);
            foreach ($data['store_labels'] as &$label) {
                $label = $filterManager->stripTags($label);
            }

            $status = $this->_objectManager->create('Magento\Sales\Model\Order\Status')->load($statusCode);
            // check if status exist
            if ($isNew && $status->getStatus()) {
                $this->messageManager->addError(__('We found another order status with the same order status code.'));
                $this->_getSession()->setFormData($data);
                $this->_redirect('sales/*/new');
                return;
            }

            $status->setData($data)->setStatus($statusCode);

            try {
                $status->save();
                $this->messageManager->addSuccess(__('You have saved the order status.'));
                $this->_redirect('sales/*/');
                return;
            } catch (\Magento\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('We couldn\'t add your order status because something went wrong saving.')
                );
            }
            $this->_getSession()->setFormData($data);
            if ($isNew) {
                $this->_redirect('sales/*/new');
            } else {
                $this->_redirect('sales/*/edit', array('status' => $this->getRequest()->getParam('status')));
            }
            return;
        }
        $this->_redirect('sales/*/');
    }

    /**
     * Assign status to state form
     *
     * @return void
     */
    public function assignAction()
    {
        $this->_title->add(__('Order Status'));
        $this->_title->add(__('Assign Order Status to State'));
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Sales::system_order_statuses');
        $this->_view->renderLayout();
    }

    /**
     * Save status assignment to state
     *
     * @return void
     */
    public function assignPostAction()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            $state = $this->getRequest()->getParam('state');
            $isDefault = $this->getRequest()->getParam('is_default');
            $status = $this->_initStatus();
            if ($status && $status->getStatus()) {
                try {
                    $status->assignState($state, $isDefault);
                    $this->messageManager->addSuccess(__('You have assigned the order status.'));
                    $this->_redirect('sales/*/');
                    return;
                } catch (\Magento\Model\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException(
                        $e,
                        __('An error occurred while assigning order status. Status has not been assigned.')
                    );
                }
            } else {
                $this->messageManager->addError(__('We can\'t find this order status.'));
            }
            $this->_redirect('sales/*/assign');
            return;
        }
        $this->_redirect('sales/*/');
    }

    /**
     * @return void
     */
    public function unassignAction()
    {
        $state = $this->getRequest()->getParam('state');
        $status = $this->_initStatus();
        if ($status) {
            try {
                $status->unassignState($state);
                $this->messageManager->addSuccess(__('You have unassigned the order status.'));
            } catch (\Magento\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while we were unassigning the order.')
                );
            }
        } else {
            $this->messageManager->addError(__('We can\'t find this order status.'));
        }
        $this->_redirect('sales/*/');
    }

    /**
     * Check current user permission on resource and privilege
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::order_statuses');
    }
}
