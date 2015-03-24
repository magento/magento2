<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Address;

use Magento\Customer\Api\Data\RegionInterface;
use Magento\Framework\Exception\InputException;

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
        $existingAddressData = [];
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

        $regionData = [
            RegionInterface::REGION_ID => $attributeValues['region_id'],
            RegionInterface::REGION => !empty($attributeValues['region']) ? $attributeValues['region'] : null,
            RegionInterface::REGION_CODE => !empty($attributeValues['region_code'])
                ? $attributeValues['region_code']
                : null,
        ];

        $region = $this->regionDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $region,
            $regionData,
            '\Magento\Customer\Api\Data\RegionInterface'
        );

        unset($attributeValues['region'], $attributeValues['region_id']);
        $attributeValues['region'] = $region;

        $addressDataObject = $this->addressDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            array_merge($existingAddressData, $attributeValues),
            '\Magento\Customer\Api\Data\AddressInterface'
        );
        $addressDataObject->setCustomerId($this->_getSession()->getCustomerId())
            ->setRegion($region)
            ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
            ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
        return $addressDataObject;
    }

    /**
     * Process address form save
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        if (!$this->getRequest()->isPost()) {
            $this->_getSession()->setAddressFormData($this->getRequest()->getPostValue());
            return $this->resultRedirectFactory->create()->setUrl(
                $this->_redirect->error($this->_buildUrl('*/*/edit'))
            );
        }

        try {
            $address = $this->_extractAddress();
            $this->_addressRepository->save($address);
            $this->messageManager->addSuccess(__('The address has been saved.'));
            $url = $this->_buildUrl('*/*/index', ['_secure' => true]);
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->success($url));
        } catch (InputException $e) {
            $this->messageManager->addError($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addError($error->getMessage());
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Cannot save address.'));
        }

        $this->_getSession()->setAddressFormData($this->getRequest()->getPostValue());
        $url = $this->_buildUrl('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->error($url));
    }
}
