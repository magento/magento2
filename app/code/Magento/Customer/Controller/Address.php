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
namespace Magento\Customer\Controller;

use Magento\App\RequestInterface;
use Magento\Exception\InputException;

/**
 * Customer address controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Address extends \Magento\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Core\App\Action\FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAddressServiceInterface
     */
    protected $_addressService;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $_formFactory;

    /**
     * @var \Magento\Customer\Service\V1\Data\RegionBuilder
     */
    protected $_regionBuilder;

    /**
     * @var \Magento\Customer\Service\V1\Data\AddressBuilder
     */
    protected $_addressBuilder;

    /**
     * @param \Magento\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     * @param \Magento\Customer\Service\V1\CustomerAddressServiceInterface $addressService
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Customer\Service\V1\Data\RegionBuilder $regionBuilder
     * @param \Magento\Customer\Service\V1\Data\AddressBuilder $addressBuilder
     * @internal param \Magento\Customer\Helper\Data $customerData
     * @internal param \Magento\Customer\Model\AddressFactory $addressFactory
     * @internal param \Magento\Customer\Model\Address\FormFactory $addressFormFactory
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator,
        \Magento\Customer\Service\V1\CustomerAddressServiceInterface $addressService,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Customer\Service\V1\Data\RegionBuilder $regionBuilder,
        \Magento\Customer\Service\V1\Data\AddressBuilder $addressBuilder
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_addressService = $addressService;
        $this->_formFactory = $formFactory;
        $this->_regionBuilder = $regionBuilder;
        $this->_addressBuilder = $addressBuilder;
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

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->_getSession()->authenticate($this)) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    /**
     * Customer addresses list
     *
     * @return void
     */
    public function indexAction()
    {
        $addresses = $this->_addressService->getAddresses($this->_getSession()->getCustomerId());
        if (count($addresses)) {
            $this->_view->loadLayout();
            $this->_view->getLayout()->initMessages();

            $block = $this->_view->getLayout()->getBlock('address_book');
            if ($block) {
                $block->setRefererUrl($this->_redirect->getRefererUrl());
            }
            $this->_view->renderLayout();
        } else {
            $this->getResponse()->setRedirect($this->_buildUrl('*/*/new'));
        }
    }

    /**
     * @return void
     */
    public function editAction()
    {
        $this->_forward('form');
    }

    /**
     * @return void
     */
    public function newAction()
    {
        $this->_forward('form');
    }

    /**
     * Address book form
     *
     * @return void
     */
    public function formAction()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $navigationBlock = $this->_view->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('customer/address');
        }
        $this->_view->renderLayout();
    }

    /**
     * Process address form save
     *
     * @return void
     */
    public function formPostAction()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->_redirect('*/*/');
            return;
        }

        if (!$this->getRequest()->isPost()) {
            $this->_getSession()->setAddressFormData($this->getRequest()->getPost());
            $this->getResponse()->setRedirect($this->_redirect->error($this->_buildUrl('*/*/edit')));
            return;
        }
        $customerId = $this->_getSession()->getCustomerId();
        try {
            $address = $this->_extractAddress();
            $this->_addressService->saveAddresses($customerId, array($address));
            $this->messageManager->addSuccess(__('The address has been saved.'));
            $url = $this->_buildUrl('*/*/index', array('_secure' => true));
            $this->getResponse()->setRedirect($this->_redirect->success($url));
            return;
        } catch (InputException $e) {
            foreach ($e->getErrors() as $error) {
                $message = InputException::translateError($error);
                $this->messageManager->addError($message);
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Cannot save address.'));
        }

        $this->_getSession()->setAddressFormData($this->getRequest()->getPost());
        $url = $this->_buildUrl('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
        $this->getResponse()->setRedirect($this->_redirect->error($url));
    }

    /**
     * Extract address from request
     *
     * @return \Magento\Customer\Service\V1\Data\Address
     */
    protected function _extractAddress()
    {
        $addressId = $this->getRequest()->getParam('id');
        $existingAddressData = array();
        if ($addressId) {
            $existingAddress = $this->_addressService->getAddress($addressId);
            if ($existingAddress->getId()) {
                $existingAddressData = \Magento\Customer\Service\V1\Data\AddressConverter::toFlatArray(
                    $existingAddress
                );
            }
        }

        /** @var \Magento\Customer\Model\Metadata\Form $addressForm */
        $addressForm = $this->_formFactory->create('customer_address', 'customer_address_edit', $existingAddressData);
        $addressData = $addressForm->extractData($this->getRequest());
        $attributeValues = $addressForm->compactData($addressData);
        $region = array('region_id' => $attributeValues['region_id'], 'region' => $attributeValues['region']);
        unset($attributeValues['region'], $attributeValues['region_id']);
        $attributeValues['region'] = $region;
        return $this->_addressBuilder->populateWithArray(
            array_merge($existingAddressData, $attributeValues)
        )->setDefaultBilling(
            $this->getRequest()->getParam('default_billing', false)
        )->setDefaultShipping(
            $this->getRequest()->getParam('default_shipping', false)
        )->create();
    }

    /**
     * @return void
     */
    public function deleteAction()
    {
        $addressId = $this->getRequest()->getParam('id', false);

        if ($addressId) {
            try {
                $address = $this->_addressService->getAddress($addressId);
                if ($address->getCustomerId() === $this->_getSession()->getCustomerId()) {
                    $this->_addressService->deleteAddress($addressId);
                    $this->messageManager->addSuccess(__('The address has been deleted.'));
                } else {
                    $this->messageManager->addError(__('An error occurred while deleting the address.'));
                }
            } catch (\Exception $other) {
                $this->messageManager->addException($other, __('An error occurred while deleting the address.'));
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
        /** @var \Magento\UrlInterface $urlBuilder */
        $urlBuilder = $this->_objectManager->create('Magento\UrlInterface');
        return $urlBuilder->getUrl($route, $params);
    }
}
