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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Recurring profiles view/management controller
 *
 * TODO: implement ACL restrictions
 */
namespace Magento\Sales\Controller\Adminhtml\Recurring;

use Magento\App\Action\NotFoundException;

class Profile extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Core\Model\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Recurring profiles list
     */
    public function indexAction()
    {
        $this->_title->add(__('Recurring Billing Profiles'));
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Sales::sales_recurring_profile');
        $this->_view->renderLayout();
    }

    /**
     * View recurring profile details
     */
    public function viewAction()
    {
        try {
            $this->_title->add(__('Recurring Billing Profiles'));
            $profile = $this->_initProfile();
            $this->_view->loadLayout();
            $this->_setActiveMenu('Magento_Sales::sales_recurring_profile');
            $this->_title->add(__('Profile #%1', $profile->getReferenceId()));
            $this->_view->renderLayout();
            return;
        } catch (\Magento\Core\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
        $this->_redirect('sales/*/');
    }

    /**
     * Profiles ajax grid
     */
    public function gridAction()
    {
        try {
            $this->_view->loadLayout()->renderLayout();
            return;
        } catch (\Magento\Core\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
        $this->_redirect('sales/*/');
    }

    /**
     * Profile orders ajax grid
     *
     * @throws NotFoundException
     */
    public function ordersAction()
    {
        try {
            $this->_initProfile();
            $this->_view->loadLayout()->renderLayout();
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Logger')->logException($e);
            throw new NotFoundException();
        }
    }

    /**
     * Profile state updater action
     */
    public function updateStateAction()
    {
        $profile = null;
        try {
            $profile = $this->_initProfile();

            switch ($this->getRequest()->getParam('action')) {
                case 'cancel':
                    $profile->cancel();
                    break;
                case 'suspend':
                    $profile->suspend();
                    break;
                case 'activate':
                    $profile->activate();
                    break;
            }
            $this->messageManager->addSuccess(__('The profile state has been updated.'));
        } catch (\Magento\Core\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We could not update the profile.'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
        if ($profile) {
            $this->_redirect('sales/*/view', array('profile' => $profile->getId()));
        } else {
            $this->_redirect('sales/*/');
        }
    }

    /**
     * Profile information updater action
     */
    public function updateProfileAction()
    {
        $profile = null;
        try {
            $profile = $this->_initProfile();
            $profile->fetchUpdate();
            if ($profile->hasDataChanges()) {
                $profile->save();
                $this->messageManager->addSuccess(__('You updated the profile.'));
            } else {
                $this->messageManager->addNotice(__('The profile has no changes.'));
            }
        } catch (\Magento\Core\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We could not update the profile.'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
        if ($profile) {
            $this->_redirect('sales/*/view', array('profile' => $profile->getId()));
        } else {
            $this->_redirect('sales/*/');
        }
    }

    /**
     * Customer billing agreements ajax action
     *
     */
    public function customerGridAction()
    {
        $this->_initCustomer();
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * Initialize customer by ID specified in request
     *
     * @return \Magento\Sales\Controller\Adminhtml\Billing\Agreement
     */
    protected function _initCustomer()
    {
        $customerId = (int) $this->getRequest()->getParam('id');
        $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        $this->_coreRegistry->register('current_customer', $customer);
        return $this;
    }

    /**
     * Load/set profile
     *
     * @return \Magento\Sales\Model\Recurring\Profile
     */
    protected function _initProfile()
    {
        $profile = $this->_objectManager->create('Magento\Sales\Model\Recurring\Profile')->load($this->getRequest()->getParam('profile'));
        if (!$profile->getId()) {
            throw new \Magento\Core\Exception(__('The profile you specified does not exist.'));
        }
        $this->_coreRegistry->register('current_recurring_profile', $profile);
        return $profile;
    }
}
