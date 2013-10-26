<?php
/**
 * Customer admin controller
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Adminhtml\Controller;

class Customer extends \Magento\Adminhtml\Controller\Action
{
    /**
     * @var \Magento\Validator
     */
    protected $_validator;

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

    /**
     * Customer initialization
     *
     * @param string $idFieldName
     * @return \Magento\Adminhtml\Controller\Customer
     */
    protected function _initCustomer($idFieldName = 'id')
    {
        // Default title
        $this->_title(__('Customers'));

        $customerId = (int)$this->getRequest()->getParam($idFieldName);
        $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
        if ($customerId) {
            $customer->load($customerId);
        }

        $this->_coreRegistry->register('current_customer', $customer);
        return $this;
    }

    /**
     * Customers list action
     */
    public function indexAction()
    {
        $this->_title(__('Customers'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->loadLayout();

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('Magento_Customer::customer_manage');

        /**
         * Append customers block to content
         */
        $this->_addContent(
            $this->getLayout()->createBlock('Magento\Adminhtml\Block\Customer', 'customer')
        );

        /**
         * Add breadcrumb item
         */
        $this->_addBreadcrumb(__('Customers'), __('Customers'));
        $this->_addBreadcrumb(__('Manage Customers'), __('Manage Customers'));

        $this->renderLayout();
    }

    /**
     * Customer grid action
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Customer edit action
     */
    public function editAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->_setActiveMenu('Magento_Customer::customer_manage');

        /* @var $customer \Magento\Customer\Model\Customer */
        $customer = $this->_coreRegistry->registry('current_customer');

        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Adminhtml\Model\Session')->getCustomerData(true);

        // restore data from SESSION
        if ($data) {
            $request = clone $this->getRequest();
            $request->setParams($data);

            if (isset($data['account'])) {
                /* @var $customerForm \Magento\Customer\Model\Form */
                $customerForm = $this->_objectManager->create('Magento\Customer\Model\Form');
                $customerForm->setEntity($customer)
                    ->setFormCode('adminhtml_customer')
                    ->setIsAjaxRequest(true);
                $formData = $customerForm->extractData($request, 'account');
                $customerForm->restoreData($formData);
            }

            if (isset($data['address']) && is_array($data['address'])) {
                /* @var $addressForm \Magento\Customer\Model\Form */
                $addressForm = $this->_objectManager->create('Magento\Customer\Model\Form');
                $addressForm->setFormCode('adminhtml_customer_address');

                foreach (array_keys($data['address']) as $addressId) {
                    if ($addressId == '_template_') {
                        continue;
                    }

                    $address = $customer->getAddressItemById($addressId);
                    if (!$address) {
                        $address = $this->_objectManager->create('Magento\Customer\Model\Address');
                        $address->setId($addressId);
                        $customer->addAddress($address);
                    }

                    $requestScope = sprintf('address/%s', $addressId);
                    $formData = $addressForm->setEntity($address)
                        ->extractData($request, $requestScope);
                    $customer->setDefaultBilling(
                        !empty($data['account']['default_billing'])
                        && $data['account']['default_billing'] == $addressId
                    );
                    $customer->setDefaultShipping(
                        !empty($data['account']['default_shipping'])
                        && $data['account']['default_shipping'] == $addressId
                    );
                    $addressForm->restoreData($formData);
                }
            }
        }

        $this->_title($customer->getId() ? $customer->getName() : __('New Customer'));

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('Magento_Customer::customer');

        $this->renderLayout();
    }

    /**
     * Create new customer action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Delete customer action
     */
    public function deleteAction()
    {
        $this->_initCustomer();
        $customer = $this->_coreRegistry->registry('current_customer');
        if ($customer->getId()) {
            try {
                $customer->delete();
                $this->_getSession()->addSuccess(
                    __('You deleted the customer.'));
            } catch (\Exception $exception){
                $this->_getSession()->addError($exception->getMessage());
            }
        }
        $this->_redirect('*/customer');
    }

    /**
     * Save customer action
     */
    public function saveAction()
    {
        $returnToEdit = false;
        $customerId = (int)$this->getRequest()->getPost('customer_id');
        $originalRequestData = $this->getRequest()->getPost();
        if ($originalRequestData) {
            try {
                // optional fields might be set in request for future processing by observers in other modules
                $accountData = $this->_extractCustomerData();
                $addressesData = $this->_extractCustomerAddressData();

                $request = $this->getRequest();

                $eventManager = $this->_eventManager;
                $beforeSaveCallback = function ($customer) use ($request, $eventManager) {
                    $eventManager->dispatch('adminhtml_customer_prepare_save', array(
                        'customer'  => $customer,
                        'request'   => $request
                    ));
                };
                $afterSaveCallback = function ($customer) use ($request, $eventManager) {
                    $eventManager->dispatch('adminhtml_customer_save_after', array(
                        'customer' => $customer,
                        'request'  => $request
                    ));
                };

                /** @var \Magento\Customer\Service\Customer $customerService */
                $customerService = $this->_objectManager->get('Magento\Customer\Service\Customer');
                $customerService->setIsAdminStore(true);
                $customerService->setBeforeSaveCallback($beforeSaveCallback);
                $customerService->setAfterSaveCallback($afterSaveCallback);
                if ($customerId) {
                    /** @var \Magento\Customer\Model\Customer $customer */
                    $customer = $customerService->update($customerId, $accountData, $addressesData);
                } else {
                    /** @var \Magento\Customer\Model\Customer $customer */
                    $customer = $customerService->create($accountData, $addressesData);
                }

                $this->_objectManager->get('Magento\Core\Model\Registry')->register('current_customer', $customer);
                $this->_getSession()->addSuccess(__('You saved the customer.'));

                $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
                $customerId = $customer->getId();
            } catch (\Magento\Validator\ValidatorException $exception) {
                $this->_addSessionErrorMessages($exception->getMessages());
                $this->_getSession()->setCustomerData($originalRequestData);
                $returnToEdit = true;
            } catch (\Magento\Core\Exception $exception) {
                $messages = $exception->getMessages(\Magento\Core\Model\Message::ERROR);
                if (!count($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setCustomerData($originalRequestData);
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->_getSession()->addException($exception,
                    __('An error occurred while saving the customer.'));
                $this->_getSession()->setCustomerData($originalRequestData);
                $returnToEdit = true;
            }
        }

        if ($returnToEdit) {
            if ($customerId) {
                $this->_redirect('*/*/edit', array('id' => $customerId, '_current' => true));
            } else {
                $this->_redirect('*/*/new', array('_current' => true));
            }
        } else {
            $this->_redirect('*/customer');
        }
    }

    /**
     * Reset password handler
     */
    public function resetPasswordAction()
    {
        $customerId = (int)$this->getRequest()->getParam('customer_id', 0);
        if (!$customerId) {
            return $this->_redirect('*/customer');
        }

        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
        $customer->load($customerId);
        if (!$customer->getId()) {
            return $this->_redirect('*/customer');
        }

        try {
            $newPasswordToken = $this->_objectManager->get('Magento\Customer\Helper\Data')
                ->generateResetPasswordLinkToken();
            $customer->changeResetPasswordLinkToken($newPasswordToken);
            $resetUrl = $this->_objectManager->create('Magento\Core\Model\Url')
                ->getUrl('customer/account/createPassword',
                    array('_query' => array('id' => $customer->getId(), 'token' => $newPasswordToken))
                );
            $customer->setResetPasswordUrl($resetUrl);
            $customer->sendPasswordReminderEmail();
            $this->_getSession()
                ->addSuccess(__('Customer will receive an email with a link to reset password.'));
        } catch (\Magento\Core\Exception $exception) {
            $messages = $exception->getMessages(\Magento\Core\Model\Message::ERROR);
            if (!count($messages)) {
                $messages = $exception->getMessage();
            }
            $this->_addSessionErrorMessages($messages);
        } catch (\Exception $exception) {
            $this->_getSession()->addException($exception,
                __('An error occurred while resetting customer password.'));
        }

        $this->_redirect('*/*/edit', array('id' => $customerId, '_current' => true));
    }

    /**
     * Add errors messages to session.
     *
     * @param array|string $messages
     */
    protected function _addSessionErrorMessages($messages)
    {
        $messages = (array)$messages;
        $session = $this->_getSession();

        $callback = function ($error) use ($session) {
            if (!($error instanceof \Magento\Core\Model\Message\Error)) {
                $error = new \Magento\Core\Model\Message\Error($error);
            }
            $session->addMessage($error);
        };
        array_walk_recursive($messages, $callback);
    }

    /**
     * Reformat customer account data to be compatible with customer service interface
     *
     * @return array
     */
    protected function _extractCustomerData()
    {
        $customerData = array();
        if ($this->getRequest()->getPost('account')) {
            $serviceAttributes = array(
                'new_password', 'default_billing', 'default_shipping', 'confirmation', 'sendemail');

            /** @var \Magento\Customer\Model\Customer $customerEntity */
            $customerEntity = $this->_objectManager->get('Magento\Customer\Model\CustomerFactory')->create();
            /** @var \Magento\Customer\Helper\Data $customerHelper */
            $customerHelper = $this->_objectManager->get('Magento\Customer\Helper\Data');
            $customerData = $customerHelper->extractCustomerData(
                $this->getRequest(), 'adminhtml_customer', $customerEntity, $serviceAttributes, 'account'
            );
        }

        if (!$this->getRequest()->getPost('customer_id')) {
            $customerData['new_password'] = 'auto';
        }
        $this->_processCustomerPassword($customerData);
        if ($this->_authorization->isAllowed(null)) {
            $customerData['is_subscribed'] = $this->getRequest()->getPost('subscription') !== null;
        }

        if (isset($customerData['disable_auto_group_change'])) {
            $customerData['disable_auto_group_change'] = empty($customerData['disable_auto_group_change']) ? '0' : '1';
        }

        return $customerData;
    }

    /**
     * Reformat customer addresses data to be compatible with customer service interface
     *
     * @return array
     */
    protected function _extractCustomerAddressData()
    {
        $addresses = $this->getRequest()->getPost('address');
        $customerData = $this->getRequest()->getPost('account');
        $result = array();
        if ($addresses) {
            if (isset($addresses['_template_'])) {
                unset($addresses['_template_']);
            }

            /** @var \Magento\Customer\Model\Address\Form $eavForm */
            $eavForm = $this->_objectManager->create('Magento\Customer\Model\Address\Form');
            /** @var \Magento\Customer\Model\Address $addressEntity */
            $addressEntity = $this->_objectManager->get('Magento\Customer\Model\AddressFactory')->create();

            $addressIdList = array_keys($addresses);
            /** @var \Magento\Customer\Helper\Data $customerHelper */
            $customerHelper = $this->_objectManager->get('Magento\Customer\Helper\Data');
            foreach ($addressIdList as $addressId) {
                $scope = sprintf('address/%s', $addressId);
                $addressData = $customerHelper->extractCustomerData(
                    $this->getRequest(), 'adminhtml_customer_address', $addressEntity, array(), $scope, $eavForm);
                if (is_numeric($addressId)) {
                    $addressData['entity_id'] = $addressId;
                }
                // Set default billing and shipping flags to address
                $addressData['is_default_billing'] = isset($customerData['default_billing'])
                    && $customerData['default_billing']
                    && $customerData['default_billing'] == $addressId;
                $addressData['is_default_shipping'] = isset($customerData['default_shipping'])
                    && $customerData['default_shipping']
                    && $customerData['default_shipping'] == $addressId;

                $result[] = $addressData;
            }
        }

        return $result;
    }

    /**
     * Generate password if auto generated password was requested
     *
     * @param array $customerData
     * @throws \Magento\Core\Exception
     */
    protected function _processCustomerPassword(&$customerData)
    {
        if (!empty($customerData['new_password'])) {
            if ($customerData['new_password'] == 'auto') {
                $customerData['autogenerate_password'] = true;
            } else {
                $customerData['password'] = $customerData['new_password'];
            }
        }
        unset($customerData['new_password']);
    }

    /**
     * Export customer grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName = 'customers.csv';
        $content = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Customer\Grid')->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export customer grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName = 'customers.xml';
        $content = $this->getLayout()->createBlock('Magento\Adminhtml\Block\Customer\Grid')->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Customer orders grid
     */
    public function ordersAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Customer last orders grid for ajax
     */
    public function lastOrdersAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Customer newsletter grid
     */
    public function newsletterAction()
    {
        $this->_initCustomer();
        $subscriber = $this->_objectManager->create('Magento\Newsletter\Model\Subscriber')
            ->loadByCustomer($this->_coreRegistry->registry('current_customer'));

        $this->_coreRegistry->register('subscriber', $subscriber);
        $this->loadLayout()->renderLayout();
    }

    public function wishlistAction()
    {
        $this->_initCustomer();
        $customer = $this->_coreRegistry->registry('current_customer');
        $itemId = (int)$this->getRequest()->getParam('delete');
        if ($customer->getId() && $itemId) {
            try {
                $this->_objectManager->create('Magento\Wishlist\Model\Item')->load($itemId)
                    ->delete();
            } catch (\Exception $exception) {
                $this->_objectManager->get('Magento\Core\Model\Logger')->logException($exception);
            }
        }

        $this->getLayout()->getUpdate()->addHandle(strtolower($this->getFullActionName()));
        $this->loadLayoutUpdates()->generateLayoutXml()->generateLayoutBlocks();
        $this->renderLayout();
    }

    /**
     * Customer last view wishlist for ajax
     */
    public function viewWishlistAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * [Handle and then] get a cart grid contents
     *
     * @return string
     */
    public function cartAction()
    {
        $this->_initCustomer();
        $websiteId = $this->getRequest()->getParam('website_id');

        // delete an item from cart
        $deleteItemId = $this->getRequest()->getPost('delete');
        if ($deleteItemId) {
            $quote = $this->_objectManager->create('Magento\Sales\Model\Quote')
                ->setWebsite(
                    $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getWebsite($websiteId)
                )
                ->loadByCustomer($this->_coreRegistry->registry('current_customer'));
            $item = $quote->getItemById($deleteItemId);
            if ($item && $item->getId()) {
                $quote->removeItem($deleteItemId);
                $quote->collectTotals()->save();
            }
        }

        $this->loadLayout();
        $this->getLayout()->getBlock('admin.customer.view.edit.cart')->setWebsiteId($websiteId);
        $this->renderLayout();
    }

    /**
     * Get shopping cart to view only
     *
     */
    public function viewCartAction()
    {
        $this->_initCustomer();
        $this->loadLayout()
            ->getLayout()
            ->getBlock('admin.customer.view.cart')
            ->setWebsiteId((int)$this->getRequest()->getParam('website_id'));
        $this->renderLayout();
    }

    /**
     * Get shopping carts from all websites for specified client
     *
     */
    public function cartsAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Get customer's product reviews list
     *
     */
    public function productReviewsAction()
    {
        $this->_initCustomer();
        $this->loadLayout()
            ->getLayout()
            ->getBlock('admin.customer.reviews')
            ->setCustomerId($this->_coreRegistry->registry('current_customer')->getId())
            ->setUseAjax(true);
        $this->renderLayout();
    }

    /**
     * AJAX customer validation action
     */
    public function validateAction()
    {
        $response = new \Magento\Object();
        $response->setError(0);

        $customer = $this->_validateCustomer($response);
        if ($customer) {
            $this->_validateCustomerAddress($response, $customer);
        }

        if ($response->getError()) {
            $this->_initLayoutMessages('Magento\Adminhtml\Model\Session');
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Customer validation
     *
     * @param \Magento\Object $response
     * @return \Magento\Customer\Model\Customer|null
     */
    protected function _validateCustomer($response)
    {
        $customer = null;
        $errors = null;

        try {
            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
            $customerId = $this->getRequest()->getParam('id');
            if ($customerId) {
                $customer->load($customerId);
            }

            /* @var $customerForm \Magento\Customer\Model\Form */
            $customerForm = $this->_objectManager->get('Magento\Customer\Model\Form');
            $customerForm->setEntity($customer)
                ->setFormCode('adminhtml_customer')
                ->setIsAjaxRequest(true)
                ->ignoreInvisible(false);
            $data = $customerForm->extractData($this->getRequest(), 'account');
            $accountData = $this->getRequest()->getPost('account');
            $data['password'] = isset($accountData['password']) ? $accountData['password'] : '';
            if (!$customer->getId()) {
                $data['password'] = $customer->generatePassword();
            }
            $data['confirmation'] = $data['password'];

            if ($customer->getWebsiteId()) {
                unset($data['website_id']);
            }

            $customer->addData($data);
            $errors = $customer->validate();
        } catch (\Magento\Core\Exception $exception) {
            /* @var $error \Magento\Core\Model\Message\Error */
            foreach ($exception->getMessages(\Magento\Core\Model\Message::ERROR) as $error) {
                $errors[] = $error->getCode();
            }
        }

        if ($errors !== true && !empty($errors)) {
            foreach ($errors as $error) {
                $this->_getSession()->addError($error);
            }
            $response->setError(1);
        }

        return $customer;
    }

    /**
     * Customer address validation.
     *
     * @param \Magento\Object $response
     * @param \Magento\Customer\Model\Customer $customer
     */
    protected function _validateCustomerAddress($response, $customer)
    {
        $addressesData = $this->getRequest()->getParam('address');
        if (is_array($addressesData)) {
            /* @var $addressForm \Magento\Customer\Model\Form */
            $addressForm = $this->_objectManager->create('Magento\Customer\Model\Form');
            $addressForm->setFormCode('adminhtml_customer_address')->ignoreInvisible(false);
            foreach (array_keys($addressesData) as $index) {
                if ($index == '_template_') {
                    continue;
                }
                $address = $customer->getAddressItemById($index);
                if (!$address) {
                    $address   = $this->_objectManager->create('Magento\Customer\Model\Address');
                }

                $requestScope = sprintf('address/%s', $index);
                $formData = $addressForm->setEntity($address)
                    ->extractData($this->getRequest(), $requestScope);

                $errors = $addressForm->validateData($formData);
                if ($errors !== true) {
                    foreach ($errors as $error) {
                        $this->_getSession()->addError($error);
                    }
                    $response->setError(1);
                }
            }
        }
    }

    /**
     * Customer mass subscribe action
     */
    public function massSubscribeAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        if (!is_array($customersIds)) {
             $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError(__('Please select customer(s).'));
        } else {
            try {
                foreach ($customersIds as $customerId) {
                    $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
                    $customer->setIsSubscribed(true);
                    $customer->save();
                }
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addSuccess(
                    __('A total of %1 record(s) were updated.', count($customersIds))
                );
            } catch (\Exception $exception) {
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError($exception->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Customer mass unsubscribe action
     */
    public function massUnsubscribeAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        if (!is_array($customersIds)) {
             $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError(__('Please select customer(s).'));
        } else {
            try {
                foreach ($customersIds as $customerId) {
                    $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
                    $customer->setIsSubscribed(false);
                    $customer->save();
                }
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addSuccess(
                    __('A total of %1 record(s) were updated.', count($customersIds))
                );
            } catch (\Exception $exception) {
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError($exception->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Customer mass delete action
     */
    public function massDeleteAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        if (!is_array($customersIds)) {
             $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError(__('Please select customer(s).'));
        } else {
            try {
                $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
                foreach ($customersIds as $customerId) {
                    $customer->reset()
                        ->load($customerId)
                        ->delete();
                }
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addSuccess(
                    __('A total of %1 record(s) were deleted.', count($customersIds))
                );
            } catch (\Exception $exception) {
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError($exception->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Customer mass assign group action
     */
    public function massAssignGroupAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        if (!is_array($customersIds)) {
             $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError(__('Please select customer(s).'));
        } else {
            try {
                foreach ($customersIds as $customerId) {
                    $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
                    $customer->setGroupId($this->getRequest()->getParam('group'));
                    $customer->save();
                }
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addSuccess(
                    __('A total of %1 record(s) were updated.', count($customersIds))
                );
            } catch (\Exception $exception) {
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError($exception->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Customer view file action
     */
    public function viewfileAction()
    {
        $file   = null;
        $plain  = false;
        if ($this->getRequest()->getParam('file')) {
            // download file
            $file   = $this->_objectManager->get('Magento\Core\Helper\Data')
                ->urlDecode($this->getRequest()->getParam('file'));
        } else if ($this->getRequest()->getParam('image')) {
            // show plain image
            $file   = $this->_objectManager->get('Magento\Core\Helper\Data')
                ->urlDecode($this->getRequest()->getParam('image'));
            $plain  = true;
        } else {
            return $this->norouteAction();
        }

        $path = $this->_objectManager->get('Magento\App\Dir')->getDir('media') . DS . 'customer';

        /** @var \Magento\Filesystem $filesystem */
        $filesystem = $this->_objectManager->get('Magento\Filesystem');
        $filesystem->setWorkingDirectory($path);
        $fileName   = $path . $file;
        if (!$filesystem->isFile($fileName)
            && !$this->_objectManager->get('Magento\Core\Helper\File\Storage')
                ->processStorageFile(str_replace('/', DS, $fileName))
        ) {
            return $this->norouteAction();
        }

        if ($plain) {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            switch (strtolower($extension)) {
                case 'gif':
                    $contentType = 'image/gif';
                    break;
                case 'jpg':
                    $contentType = 'image/jpeg';
                    break;
                case 'png':
                    $contentType = 'image/png';
                    break;
                default:
                    $contentType = 'application/octet-stream';
                    break;
            }

            $contentLength = $filesystem->getFileSize($fileName);
            $contentModify = $filesystem->getMTime($fileName);

            $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Content-type', $contentType, true)
                ->setHeader('Content-Length', $contentLength)
                ->setHeader('Last-Modified', date('r', $contentModify))
                ->clearBody();
            $this->getResponse()->sendHeaders();

            echo $filesystem->read($fileName);
        } else {
            $name = pathinfo($fileName, PATHINFO_BASENAME);
            $this->_prepareDownloadResponse($name, array(
                'type'  => 'filename',
                'value' => $fileName
            ));
        }

        exit();
    }

    /**
     * Customer access rights checking
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Customer::manage');
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array
     * @return array
     */
    protected function _filterPostData($data)
    {
        $data['account'] = $this->_filterDates($data['account'], array('dob'));
        return $data;
    }
}
