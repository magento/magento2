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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Recurring profiles view/management controller
 */
namespace Magento\RecurringProfile\Controller;

use Magento\App\RequestInterface;

class RecurringProfile extends \Magento\App\Action\Action
{
    /**
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_session = null;

    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\App\Action\Title
     */
    protected $_title;

    /**
     * @param \Magento\App\Action\Context $context
     * @param \Magento\Registry $coreRegistry
     * @param \Magento\App\Action\Title $title
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Registry $coreRegistry,
        \Magento\App\Action\Title $title
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->_title = $title;
    }

    /**
     * Make sure customer is logged in and put it into registry
     *
     * @param RequestInterface $request
     * @return \Magento\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$request->isDispatched()) {
            return parent::dispatch($request);
        }
        $this->_session = $this->_objectManager->get('Magento\Customer\Model\Session');
        if (!$this->_session->authenticate($this)) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        $this->_coreRegistry->register('current_customer', $this->_session->getCustomer());
        return parent::dispatch($request);
    }

    /**
     * Profiles listing
     */
    public function indexAction()
    {
        $this->_title->add(__('Recurring Billing Profiles'));
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    /**
     * Profile main view
     */
    public function viewAction()
    {
        $this->_viewAction();
    }

    /**
     * Profile related orders view
     */
    public function ordersAction()
    {
        $this->_viewAction();
    }

    /**
     * Attempt to set profile state
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
                default:
                    break;
            }
            $this->messageManager->addSuccess(__('The profile state has been updated.'));
        } catch (\Magento\Core\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We couldn\'t update the profile.'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
        if ($profile) {
            $this->_redirect('*/*/view', array('profile' => $profile->getId()));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Fetch an update with profile
     */
    public function updateProfileAction()
    {
        $profile = null;
        try {
            $profile = $this->_initProfile();
            $profile->fetchUpdate();
            if ($profile->hasDataChanges()) {
                $profile->save();
                $this->messageManager->addSuccess(__('The profile has been updated.'));
            } else {
                $this->messageManager->addNotice(__('The profile has no changes.'));
            }
        } catch (\Magento\Core\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We couldn\'t update the profile.'));
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
        if ($profile) {
            $this->_redirect('*/*/view', array('profile' => $profile->getId()));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Generic profile view action
     */
    protected function _viewAction()
    {
        try {
            $profile = $this->_initProfile();
            $this->_title->add(__('Recurring Billing Profiles'));
            $this->_title->add(__('Profile #%1', $profile->getReferenceId()));
            $this->_view->loadLayout();
            $this->_view->getLayout()->initMessages();
            $this->_view->renderLayout();
            return;
        } catch (\Magento\Core\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
        $this->_redirect('*/*/');
    }

    /**
     * Instantiate current profile and put it into registry
     *
     * @return \Magento\RecurringProfile\Model\Profile
     * @throws \Magento\Core\Exception
     */
    protected function _initProfile()
    {
        $profile = $this->_objectManager->create('Magento\RecurringProfile\Model\Profile')
            ->load($this->getRequest()->getParam('profile'));
        if (!$profile->getId()) {
            throw new \Magento\Core\Exception(__('We can\'t find the profile you specified.'));
        }
        $this->_coreRegistry->register('current_recurring_profile', $profile);
        return $profile;
    }
}
