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
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer address controller
 *
 * @category   Magento
 * @package    Magento_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Controller;

class Address extends \Magento\Core\Controller\Front\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var \Magento\Customer\Model\Address\FormFactory
     */
    protected $_addressFormFactory;

    public function __construct(
        \Magento\Core\Controller\Varien\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\Address\FormFactory $addressFormFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_addressFactory = $addressFactory;
        $this->_addressFormFactory = $addressFormFactory;
        parent::__construct($context);
    }

    /**
     * Retrieve customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->_getSession()->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    /**
     * Customer addresses list
     */
    public function indexAction()
    {
        if (count($this->_getSession()->getCustomer()->getAddresses())) {
            $this->loadLayout();
            $this->_initLayoutMessages('Magento\Customer\Model\Session');
            $this->_initLayoutMessages('Magento\Catalog\Model\Session');

            $block = $this->getLayout()->getBlock('address_book');
            if ($block) {
                $block->setRefererUrl($this->_getRefererUrl());
            }
            $this->renderLayout();
        } else {
            $this->getResponse()->setRedirect($this->_buildUrl('*/*/new'));
        }
    }

    public function editAction()
    {
        $this->_forward('form');
    }

    public function newAction()
    {
        $this->_forward('form');
    }

    /**
     * Address book form
     */
    public function formAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('Magento\Customer\Model\Session');
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('customer/address');
        }
        $this->renderLayout();
    }

    /**
     * Process address form save
     */
    public function formPostAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
        }

        if (!$this->getRequest()->isPost()) {
            $this->_getSession()->setAddressFormData($this->getRequest()->getPost());
            $this->_redirectError($this->_buildUrl('*/*/edit'));
            return;
        }

        try {
            $address = $this->_extractAddress();
            $this->_validateAddress($address);
            $address->save();
            $this->_getSession()->addSuccess(__('The address has been saved.'));
            $this->_redirectSuccess($this->_buildUrl('*/*/index', array('_secure'=>true)));
            return;
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addException($e, $e->getMessage());
        } catch (\Magento\Validator\ValidatorException $e) {
            foreach ($e->getMessages() as $messages) {
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }
        } catch (\Exception $e) {
            $this->_getSession()->addException($e, __('Cannot save address.'));
        }

        $this->_getSession()->setAddressFormData($this->getRequest()->getPost());
        $this->_redirectError($this->_buildUrl('*/*/edit', array('id' => $address->getId())));
    }

    /**
     * Do address validation using validate methods in models
     *
     * @param \Magento\Customer\Model\Address $address
     * @throws \Magento\Validator\ValidatorException
     */
    protected function _validateAddress($address)
    {
        $addressErrors = $address->validate();
        if (is_array($addressErrors) && count($addressErrors) > 0) {
            throw new \Magento\Validator\ValidatorException(array($addressErrors));
        }
    }

    /**
     * Extract address from request
     *
     * @return \Magento\Customer\Model\Address
     */
    protected function _extractAddress()
    {
        $customer = $this->_getSession()->getCustomer();
        /* @var \Magento\Customer\Model\Address $address */
        $address  = $this->_createAddress();
        $addressId = $this->getRequest()->getParam('id');
        if ($addressId) {
            $existsAddress = $customer->getAddressById($addressId);
            if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
                $address->load($existsAddress->getId());
            }
        }
        /* @var \Magento\Customer\Model\Form $addressForm */
        $addressForm = $this->_createAddressForm();
        $addressForm->setFormCode('customer_address_edit')
            ->setEntity($address);
        $addressData = $addressForm->extractData($this->getRequest());
        $addressForm->compactData($addressData);
        $address->setCustomerId($customer->getId())
            ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
            ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
        return $address;
    }

    public function deleteAction()
    {
        $addressId = $this->getRequest()->getParam('id', false);

        if ($addressId) {
            $address = $this->_createAddress();
            $address->load($addressId);

            // Validate address_id <=> customer_id
            if ($address->getCustomerId() != $this->_getSession()->getCustomerId()) {
                $this->_getSession()->addError(__('The address does not belong to this customer.'));
                $this->getResponse()->setRedirect($this->_buildUrl('*/*/index'));
                return;
            }

            try {
                $address->delete();
                $this->_getSession()->addSuccess(__('The address has been deleted.'));
            } catch (\Exception $e){
                $this->_getSession()->addException($e, __('An error occurred while deleting the address.'));
            }
        }
        $this->getResponse()->setRedirect($this->_buildUrl('*/*/index'));
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _buildUrl($route = '', $params = array())
    {
        /** @var \Magento\Core\Model\Url $urlBuilder */
        $urlBuilder = $this->_objectManager->create('Magento\Core\Model\Url');
        return $urlBuilder->getUrl($route, $params);
    }

    /**
     * @return \Magento\Customer\Model\Address
     */
    protected function _createAddress()
    {
        return $this->_addressFactory->create();
    }

    /**
     * @return \Magento\Customer\Model\Address\Form
     */
    protected function _createAddressForm()
    {
        return $this->_addressFormFactory->create();
    }
}
