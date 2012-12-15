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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Adminhtml_CustomerController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var Magento_Validator
     */
    protected $_validator;

    /**
     * Customer initialization
     *
     * @param string $idFieldName
     * @return Mage_Adminhtml_CustomerController
     */
    protected function _initCustomer($idFieldName = 'id')
    {
        // Default title
        $this->_title($this->__('Customers'))->_title($this->__('Manage Customers'));

        $customerId = (int)$this->getRequest()->getParam($idFieldName);
        $customer = Mage::getModel('Mage_Customer_Model_Customer');
        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);
        return $this;
    }

    /**
     * Customers list action
     */
    public function indexAction()
    {
        $this->_title($this->__('Customers'))->_title($this->__('Manage Customers'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->loadLayout();

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('Mage_Customer::customer_manage');

        /**
         * Append customers block to content
         */
        $this->_addContent(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Customer', 'customer')
        );

        /**
         * Add breadcrumb item
         */
        $this->_addBreadcrumb(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Customers'),
            Mage::helper('Mage_Adminhtml_Helper_Data')->__('Customers'));
        $this->_addBreadcrumb(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Manage Customers'),
            Mage::helper('Mage_Adminhtml_Helper_Data')->__('Manage Customers'));

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

        /* @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::registry('current_customer');

        // set entered data if was error when we do save
        $data = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getCustomerData(true);

        // restore data from SESSION
        if ($data) {
            $request = clone $this->getRequest();
            $request->setParams($data);

            if (isset($data['account'])) {
                /* @var $customerForm Mage_Customer_Model_Form */
                $customerForm = Mage::getModel('Mage_Customer_Model_Form');
                $customerForm->setEntity($customer)
                    ->setFormCode('adminhtml_customer')
                    ->setIsAjaxRequest(true);
                $formData = $customerForm->extractData($request, 'account');
                $customerForm->restoreData($formData);
            }

            if (isset($data['address']) && is_array($data['address'])) {
                /* @var $addressForm Mage_Customer_Model_Form */
                $addressForm = Mage::getModel('Mage_Customer_Model_Form');
                $addressForm->setFormCode('adminhtml_customer_address');

                foreach (array_keys($data['address']) as $addressId) {
                    if ($addressId == '_template_') {
                        continue;
                    }

                    $address = $customer->getAddressItemById($addressId);
                    if (!$address) {
                        $address = Mage::getModel('Mage_Customer_Model_Address');
                        $customer->addAddress($address);
                    }

                    $formData = $addressForm->setEntity($address)
                        ->extractData($request);
                    $addressForm->restoreData($formData);
                }
            }
        }

        $this->_title($customer->getId() ? $customer->getName() : $this->__('New Customer'));

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('Mage_Customer::customer');

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
        $customer = Mage::registry('current_customer');
        if ($customer->getId()) {
            try {
                $customer->delete();
                $this->_getSession()->addSuccess(
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('The customer has been deleted.'));
            }
            catch (Exception $exception){
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
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = null;
        $returnToEdit = false;
        $customerId = (int)$this->getRequest()->getPost('customer_id');
        $originalRequestData = $this->getRequest()->getPost();
        if ($originalRequestData) {
            try {
                // optional fields might be set in request for future processing by observers in other modules
                $accountData = $this->_extractCustomerData();
                $addressesData = $this->_extractCustomerAddressData();

                $request = $this->getRequest();
                /** @var Mage_Core_Model_Event_Manager $eventManager */
                $eventManager = $this->_objectManager->get('Mage_Core_Model_Event_Manager');
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

                /** @var Mage_Customer_Service_Customer $customerService */
                $customerService = $this->_objectManager->get('Mage_Customer_Service_Customer');
                $customerService->setIsAdminStore(true);
                $customerService->setBeforeSaveCallback($beforeSaveCallback);
                $customerService->setAfterSaveCallback($afterSaveCallback);
                if ($customerId) {
                    $customer = $customerService->update($customerId, $accountData, $addressesData);
                } else {
                    $customer = $customerService->create($accountData, $addressesData);
                }

                $this->_objectManager->get('Mage_Core_Model_Registry')->register('current_customer', $customer);
                $this->_getSession()->addSuccess($this->_getHelper()->__('The customer has been saved.'));

                $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
                $customerId = $customer->getId();
            } catch (Magento_Validator_Exception $exception) {
                $this->_addSessionErrorMessages($exception->getMessages());
                $this->_getSession()->setCustomerData($originalRequestData);
                $returnToEdit = true;
            } catch (Mage_Core_Exception $exception) {
                $messages = $exception->getMessages(Mage_Core_Model_Message::ERROR);
                if (!count($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $this->_getSession()->setCustomerData($originalRequestData);
                $returnToEdit = true;
            } catch (Exception $exception) {
                $this->_getSession()->addException($exception,
                    $this->_getHelper()->__('An error occurred while saving the customer.'));
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
     * Add errors messages to session.
     *
     * @param array|string $messages
     */
    protected function _addSessionErrorMessages($messages)
    {
        $messages = (array)$messages;
        $session = $this->_getSession();

        $callback = function ($error) use ($session) {
            if (!($error instanceof Mage_Core_Model_Message_Error)) {
                $error = new Mage_Core_Model_Message_Error($error);
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
                'password', 'new_password', 'default_billing', 'default_shipping', 'confirmation');

            /** @var Mage_Customer_Model_Customer $customerEntity */
            $customerEntity = $this->_objectManager
                ->get('Mage_Customer_Model_Customer_Factory')
                ->create();
            /** @var Mage_Customer_Helper_Data $customerHelper */
            $customerHelper = $this->_objectManager->get('Mage_Customer_Helper_Data');
            $customerData = $customerHelper->extractCustomerData(
                $this->getRequest(), 'adminhtml_customer', $customerEntity, $serviceAttributes, 'account');
        }

        $this->_processCustomerPassword($customerData);
        /** @var Mage_Core_Model_Authorization $acl */
        $acl = $this->_objectManager->get('Mage_Core_Model_Authorization');
        if ($acl->isAllowed(Mage_Backend_Model_Acl_Config::ACL_RESOURCE_ALL)) {
            $customerData['is_subscribed'] = (bool)$this->getRequest()->getPost('subscription', false);
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

            /** @var Mage_Customer_Model_Address_Form $eavForm */
            $eavForm = $this->_objectManager->create('Mage_Customer_Model_Address_Form');
            /** @var Mage_Customer_Model_Address $addressEntity */
            $addressEntity = $this->_objectManager
                ->get('Mage_Customer_Model_Address_Factory')
                ->create();

            $addressIdList = array_keys($addresses);
            /** @var Mage_Customer_Helper_Data $customerHelper */
            $customerHelper = $this->_objectManager->get('Mage_Customer_Helper_Data');
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
     * @throws Mage_Core_Exception
     */
    protected function _processCustomerPassword(&$customerData)
    {
        if (isset($customerData['new_password']) && $customerData['new_password'] !== false) {
            $customerData['password'] = $customerData['new_password'];
            unset($customerData['new_password']);
        }
        if (isset($customerData['password']) && ($customerData['password'] == 'auto')) {
            unset($customerData['password']);
            $customerData['autogenerate_password'] = true;
        }
    }

    /**
     * Export customer grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName = 'customers.csv';
        $content = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Customer_Grid')->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export customer grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'customers.xml';
        $content    = $this->getLayout()->createBlock('Mage_Adminhtml_Block_Customer_Grid')
            ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Customer orders grid
     *
     */
    public function ordersAction() {
        $this->_initCustomer();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Customer last orders grid for ajax
     *
     */
    public function lastOrdersAction() {
        $this->_initCustomer();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Customer newsletter grid
     *
     */
    public function newsletterAction()
    {
        $this->_initCustomer();
        $subscriber = Mage::getModel('Mage_Newsletter_Model_Subscriber')
            ->loadByCustomer(Mage::registry('current_customer'));

        Mage::register('subscriber', $subscriber);
        $this->loadLayout()
            ->renderLayout();
    }

    public function wishlistAction()
    {
        $this->_initCustomer();
        $customer = Mage::registry('current_customer');
        if ($customer->getId()) {
            if($itemId = (int) $this->getRequest()->getParam('delete')) {
                try {
                    Mage::getModel('Mage_Wishlist_Model_Item')->load($itemId)
                        ->delete();
                }
                catch (Exception $exception) {
                    Mage::logException($exception);
                }
            }
        }

        $this->getLayout()->getUpdate()
            ->addHandle(strtolower($this->getFullActionName()));
        $this->loadLayoutUpdates()->generateLayoutXml()->generateLayoutBlocks();

        $this->renderLayout();
    }

    /**
     * Customer last view wishlist for ajax
     *
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
            $quote = Mage::getModel('Mage_Sales_Model_Quote')
                ->setWebsite(Mage::app()->getWebsite($websiteId))
                ->loadByCustomer(Mage::registry('current_customer'));
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
            ->setCustomerId(Mage::registry('current_customer')->getId())
            ->setUseAjax(true);
        $this->renderLayout();
    }

    /**
     * AJAX customer validation action
     */
    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(0);

        $customer = $this->_validateCustomer($response);
        if ($customer) {
            $this->_validateCustomerAddress($response, $customer);
        }

        if ($response->getError()) {
            $this->_initLayoutMessages('Mage_Adminhtml_Model_Session');
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Customer validation
     *
     * @param Varien_Object $response
     * @return Mage_Customer_Model_Customer|null
     */
    protected function _validateCustomer($response)
    {
        $customer = null;
        $errors = null;

        try {
            /** @var Mage_Customer_Model_Customer $customer */
            $customer = $this->_objectManager->create('Mage_Customer_Model_Customer');
            $customerId = $this->getRequest()->getParam('id');
            if ($customerId) {
                $customer->load($customerId);
            }

            /* @var $customerForm Mage_Customer_Model_Form */
            $customerForm = $this->_objectManager->get('Mage_Customer_Model_Form');
            $customerForm->setEntity($customer)
                ->setFormCode('adminhtml_customer')
                ->setIsAjaxRequest(true)
                ->ignoreInvisible(false);
            $data = $customerForm->extractData($this->getRequest(), 'account');
            $accountData = $this->getRequest()->getPost('account');
            $this->_processCustomerPassword($accountData);
            if (isset($accountData['autogenerate_password'])) {
                $data['password'] = $customer->generatePassword();
            } else {
                $data['password'] = $accountData['password'];
            }
            $data['confirmation'] = $data['password'];

            if ($customer->getWebsiteId()) {
                unset($data['website_id']);
            }

            $customer->addData($data);
            $errors = $customer->validate();
        } catch (Mage_Core_Exception $exception) {
            /* @var $error Mage_Core_Model_Message_Error */
            foreach ($exception->getMessages(Mage_Core_Model_Message::ERROR) as $error) {
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
     * @param Varien_Object $response
     * @param Mage_Customer_Model_Customer $customer
     */
    protected function _validateCustomerAddress($response, $customer)
    {
        $addressesData = $this->getRequest()->getParam('address');
        if (is_array($addressesData)) {
            /* @var $addressForm Mage_Customer_Model_Form */
            $addressForm = Mage::getModel('Mage_Customer_Model_Form');
            $addressForm->setFormCode('adminhtml_customer_address')->ignoreInvisible(false);
            foreach (array_keys($addressesData) as $index) {
                if ($index == '_template_') {
                    continue;
                }
                $address = $customer->getAddressItemById($index);
                if (!$address) {
                    $address   = Mage::getModel('Mage_Customer_Model_Address');
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
        if(!is_array($customersIds)) {
             Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Please select customer(s).'));
        } else {
            try {
                foreach ($customersIds as $customerId) {
                    $customer = Mage::getModel('Mage_Customer_Model_Customer')->load($customerId);
                    $customer->setIsSubscribed(true);
                    $customer->save();
                }
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('Total of %d record(s) were updated.', count($customersIds))
                );
            } catch (Exception $exception) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($exception->getMessage());
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
        if(!is_array($customersIds)) {
             Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Please select customer(s).'));
        } else {
            try {
                foreach ($customersIds as $customerId) {
                    $customer = Mage::getModel('Mage_Customer_Model_Customer')->load($customerId);
                    $customer->setIsSubscribed(false);
                    $customer->save();
                }
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('Total of %d record(s) were updated.', count($customersIds))
                );
            } catch (Exception $exception) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($exception->getMessage());
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
        if(!is_array($customersIds)) {
             Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Please select customer(s).'));
        } else {
            try {
                $customer = Mage::getModel('Mage_Customer_Model_Customer');
                foreach ($customersIds as $customerId) {
                    $customer->reset()
                        ->load($customerId)
                        ->delete();
                }
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('Total of %d record(s) were deleted.', count($customersIds))
                );
            } catch (Exception $exception) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($exception->getMessage());
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
        if(!is_array($customersIds)) {
             Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Please select customer(s).'));
        } else {
            try {
                foreach ($customersIds as $customerId) {
                    $customer = Mage::getModel('Mage_Customer_Model_Customer')->load($customerId);
                    $customer->setGroupId($this->getRequest()->getParam('group'));
                    $customer->save();
                }
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('Total of %d record(s) were updated.', count($customersIds))
                );
            } catch (Exception $exception) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($exception->getMessage());
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
            $file   = Mage::helper('Mage_Core_Helper_Data')->urlDecode($this->getRequest()->getParam('file'));
        } else if ($this->getRequest()->getParam('image')) {
            // show plain image
            $file   = Mage::helper('Mage_Core_Helper_Data')->urlDecode($this->getRequest()->getParam('image'));
            $plain  = true;
        } else {
            return $this->norouteAction();
        }

        $path = Mage::getBaseDir('media') . DS . 'customer';

        $ioFile = new Varien_Io_File();
        $ioFile->open(array('path' => $path));
        $fileName   = $ioFile->getCleanPath($path . $file);
        $path       = $ioFile->getCleanPath($path);

        if ((!$ioFile->fileExists($fileName) || strpos($fileName, $path) !== 0)
            && !Mage::helper('Mage_Core_Helper_File_Storage')->processStorageFile(str_replace('/', DS, $fileName))
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

            $ioFile->streamOpen($fileName, 'r');
            $contentLength = $ioFile->streamStat('size');
            $contentModify = $ioFile->streamStat('mtime');

            $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Content-type', $contentType, true)
                ->setHeader('Content-Length', $contentLength)
                ->setHeader('Last-Modified', date('r', $contentModify))
                ->clearBody();
            $this->getResponse()->sendHeaders();

            while (false !== ($buffer = $ioFile->streamRead())) {
                echo $buffer;
            }
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
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Customer::manage');
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
