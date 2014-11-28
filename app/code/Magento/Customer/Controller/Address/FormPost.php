<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Controller\Address;

use Magento\Framework\Exception\InputException;
use Magento\Customer\Api\Data\RegionInterface;

class FormPost extends \Magento\Customer\Controller\Address
{
    /**
     * Extract address from request
     *
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    protected function _extractAddress()
    {
        $addressId = $this->getRequest()->getParam('id');
        $existingAddressData = array();
        if ($addressId) {
            $existingAddress = $this->_addressRepository->getById($addressId);

            $existingAddressData = $this->_dataProcessor
                ->buildOutputDataArray($existingAddress, '\Magento\Customer\Api\Data\AddressInterface');

            $region = $existingAddress->getRegion()->getRegion();
            $existingAddressData['region_code'] = $existingAddress->getRegion()->getRegionCode();
            $existingAddressData['region_id'] = $existingAddress->getRegion()->getRegionId();
            $existingAddressData['region'] = $region;
        }

        /** @var \Magento\Customer\Model\Metadata\Form $addressForm */
        $addressForm = $this->_formFactory->create('customer_address', 'customer_address_edit', $existingAddressData);
        $addressData = $addressForm->extractData($this->getRequest());
        $attributeValues = $addressForm->compactData($addressData);

        $region = [
            RegionInterface::REGION_ID => $attributeValues['region_id'],
            RegionInterface::REGION => !empty($attributeValues['region']) ? $attributeValues['region'] : null,
            RegionInterface::REGION_CODE => !empty($attributeValues['region_code'])
                ? $attributeValues['region_code']
                : null
        ];

        $region = $this->_regionDataBuilder
            ->populateWithArray($region)
            ->create();

        unset($attributeValues['region'], $attributeValues['region_id']);
        $attributeValues['region'] = $region;

        return $this->_addressDataBuilder
            ->populateWithArray(array_merge($existingAddressData, $attributeValues))
            ->setCustomerId($this->_getSession()->getCustomerId())
            ->setRegion($region)
            ->setDefaultBilling($this->getRequest()->getParam('default_billing', false))
            ->setDefaultShipping($this->getRequest()->getParam('default_shipping', false))
            ->create();
    }

    /**
     * Process address form save
     *
     * @return void
     */
    public function execute()
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

        try {
            $address = $this->_extractAddress();
            $this->_addressRepository->save($address);
            $this->messageManager->addSuccess(__('The address has been saved.'));
            $url = $this->_buildUrl('*/*/index', array('_secure' => true));
            $this->getResponse()->setRedirect($this->_redirect->success($url));
            return;
        } catch (InputException $e) {
            $this->messageManager->addError($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addError($error->getMessage());
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Cannot save address.'));
        }

        $this->_getSession()->setAddressFormData($this->getRequest()->getPost());
        $url = $this->_buildUrl('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
        $this->getResponse()->setRedirect($this->_redirect->error($url));
    }
}
