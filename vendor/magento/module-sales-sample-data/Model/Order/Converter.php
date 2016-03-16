<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSampleData\Model\Order;

use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class Converter
 */
class Converter
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\CatalogSampleData\Model\Product\Converter
     */
    protected $productConverter;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @param CustomerRepositoryInterface $customerAccount
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\ConfigurableSampleData\Model\Product\ConverterFactory $productConverterFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        CustomerRepositoryInterface $customerAccount,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableSampleData\Model\Product\ConverterFactory $productConverterFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->customerRepository = $customerAccount;
        $this->productFactory = $productFactory;
        $this->productConverter = $productConverterFactory->create();
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param array $row
     * @return array
     */
    public function convertRow($row)
    {
        foreach ($row as $field => $value) {
            switch ($field) {
                case 'customer_email':
                    $data['order']['account'] = $this->getAccountInformation($value);
                    if (isset($data['order']['account']['billing_address'])) {
                        $data['order']['billing_address'] = $data['order']['account']['billing_address'];
                        unset($data['order']['account']['billing_address']);
                    }
                    if (isset($data['order']['account']['shipping_address'])) {
                        $data['order']['shipping_address'] = $data['order']['account']['shipping_address'];
                        unset($data['order']['account']['shipping_address']);
                    }
                    break;
                case 'customer_note':
                    $data['order']['comment']['customer_note'] = $value;
                    break;
                case 'product':
                    $data['add_products'] = $this->convertProductData($value);
                    break;
                case 'payment':
                    $data['payment']['method'] = $value;
                    break;
                case 'refund':
                    $data['refund'] = $value;
                    break;
                default:
                    $data['order'][$field] = $value;
                    break;
            }
        }
        $data['customer_note'] = '';
        return $data;
    }

    /**
     * @param string $productSku
     * @return \Magento\Framework\DataObject
     */
    protected function getProductData($productSku)
    {
        $product = $this->productFactory
            ->create()
            ->getCollection()
            ->clear()
            ->addFieldToFilter('sku', $productSku)
            ->addAttributeToSelect('*')
            ->getFirstItem();

        $product->loadByAttribute('sku', $productSku);

        return $product;
    }

    /**
     * @param string $email
     * @return array
     */
    protected function getAccountInformation($email)
    {
        $customer = $this->customerRepository->get($email);
        $account = [
            'email' => $customer->getEmail(),
            'group_id' => $customer->getGroupId()
        ];
        foreach ($customer->getAddresses() as $customerAddress) {
            if ($customerAddress->isDefaultBilling()) {
                $account['billing_address'] = $this->getAddresses($customerAddress);
            }
            if ($customerAddress->isDefaultShipping()) {
                $account['shipping_address'] = $this->getAddresses($customerAddress);
            }
        }
        return $account;
    }

    /**
     * @param \Magento\Customer\Api\Data\AddressInterface $addressData
     * @return array
     */
    protected function getAddresses(\Magento\Customer\Api\Data\AddressInterface $addressData)
    {
        $addressData = [
            'customer_address_id' => $addressData->getId(),
            'prefix' => $addressData->getPrefix(),
            'firstname' => $addressData->getFirstname(),
            'middlename' => $addressData->getMiddlename(),
            'lastname' => $addressData->getLastname(),
            'suffix' => $addressData->getSuffix(),
            'company' => $addressData->getCompany(),
            'street' => $addressData->getStreet(),
            'city' => $addressData->getCity(),
            'country_id' => $addressData->getCountryId(),
            'region' => $addressData->getRegion()->getRegion(),
            'region_id' => $addressData->getRegion()->getRegionId(),
            'postcode' => $addressData->getPostcode(),
            'telephone' => $addressData->getTelephone(),
            'fax' => $addressData->getFax(),
            'vat_id' => $addressData->getVatId()
        ];
        return array_filter($addressData);
    }

    /**
     * @param array $productData
     * @return array
     */
    protected function convertProductData($productData)
    {
        $productValues = unserialize($productData);
        $productId = $this->getProductData($productValues['sku'])->getId();
        $productData = ['qty' => $productValues['qty']];
        if (isset($productValues['configurable_options'])) {
            $productData['super_attribute'] = $this->getProductAttributes($productValues['configurable_options']);
        }
        return [$productId => $productData];

    }

    /**
     * @param array $configurableAttributes
     * @return array
     */
    protected function getProductAttributes($configurableAttributes)
    {
        $attributesData = [];
        foreach ($configurableAttributes as $attributeCode => $value) {
            $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
            if (!$attribute->getId()) {
                continue;
            }
            $options = $this->productConverter->getAttributeOptions($attribute->getAttributeCode());
            $attributeOption = $options->getItemByColumnValue('value', $value);
            $attributeId = $attributeOption->getDataByKey('attribute_id');
            $attributesData[$attributeId] = $attributeOption->getDataByKey('option_id');
        }
        return $attributesData;
    }
}
