<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CustomerSampleData\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;
use Magento\Customer\Api\Data\RegionInterface;

/**
 * Class Customer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Customer
{
    /**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
    protected $fixtureManager;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * @var \Magento\Customer\Api\Data\RegionInterfaceFactory
     */
    protected $regionFactory;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var array $customerDataProfile
     */
    protected $customerDataProfile;

    /**
     * @var array $customerDataAddress
     */
    protected $customerDataAddress;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;


    protected $appState;

    /**
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory
     * @param \Magento\Customer\Api\Data\RegionInterfaceFactory $regionFactory
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\App\State $appState
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionFactory,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\App\State $appState
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->countryFactory = $countryFactory;
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
        $this->accountManagement = $accountManagement;
        $this->storeManager = $storeManager;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->appState = $appState;
    }

    /**
     * {@inheritdoc}
     */
    public function install($fixtures)
    {
        foreach ($fixtures as $fixture) {
            $filePath = $this->fixtureManager->getFixture($fixture);
            $rows = $this->csvReader->getData($filePath);
            $header = array_shift($rows);
            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $row = $data;
                // Collect customer profile and addresses data
                $customerData['profile'] = $this->convertRowData($row, $this->getDefaultCustomerProfile());
                if (!$this->accountManagement->isEmailAvailable($customerData['profile']['email'])) {
                    continue;
                }
                $customerData['address'] = $this->convertRowData($row, $this->getDefaultCustomerAddress());
                $customerData['address']['region_id'] = $this->getRegionId($customerData['address']);

                $address = $customerData['address'];
                $regionData = [
                    RegionInterface::REGION_ID => $address['region_id'],
                    RegionInterface::REGION => !empty($address['region']) ? $address['region'] : null,
                    RegionInterface::REGION_CODE => !empty($address['region_code']) ? $address['region_code'] : null,
                ];
                $region = $this->regionFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $region,
                    $regionData,
                    '\Magento\Customer\Api\Data\RegionInterface'
                );

                $addresses = $this->addressFactory->create();
                unset($customerData['address']['region']);
                $this->dataObjectHelper->populateWithArray(
                    $addresses,
                    $customerData['address'],
                    '\Magento\Customer\Api\Data\AddressInterface'
                );
                $addresses->setRegion($region)
                    ->setIsDefaultBilling(true)
                    ->setIsDefaultShipping(true);

                $customer = $this->customerFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $customer,
                    $customerData['profile'],
                    '\Magento\Customer\Api\Data\CustomerInterface'
                );
                $customer->setAddresses([$addresses]);
                $this->appState->emulateAreaCode(
                    'frontend',
                    [$this->accountManagement, 'createAccount'],
                    [$customer, $row['password']]
                );
            }
        }
    }

    /**
     * @return array
     */
    protected function getDefaultCustomerProfile()
    {
        if (!$this->customerDataProfile) {
            $this->customerDataProfile = [
                'website_id' => $this->storeManager->getWebsite()->getId(),
                'group_id' => $this->storeManager->getGroup()->getId(),
                'disable_auto_group_change' => '0',
                'prefix',
                'firstname' => '',
                'middlename' => '',
                'lastname' => '',
                'suffix' => '',
                'email' => '',
                'dob' => '',
                'taxvat' => '',
                'gender' => '',
                'confirmation' => false,
                'sendemail' => false,
            ];
        }
        return $this->customerDataProfile;
    }

    /**
     * @return array
     */
    protected function getDefaultCustomerAddress()
    {
        if (!$this->customerDataAddress) {
            $this->customerDataAddress = [
                'prefix' => '',
                'firstname' => '',
                'middlename' => '',
                'lastname' => '',
                'suffix' => '',
                'company' => '',
                'street' => [
                    0 => '',
                    1 => '',
                ],
                'city' => '',
                'country_id' => '',
                'region' => '',
                'postcode' => '',
                'telephone' => '',
                'fax' => '',
                'vat_id' => '',
                'default_billing' => true,
                'default_shipping' => true,
            ];
        }
        return $this->customerDataAddress;
    }

    /**
     * @param array $row
     * @param array $data
     * @return array $data
     */
    protected function convertRowData($row, $data)
    {
        foreach ($row as $field => $value) {
            if (isset($data[$field])) {
                if ($field == 'street') {
                    $data[$field] = unserialize($value);
                    continue;
                }
                if ($field == 'password') {
                    continue;
                }
                $data[$field] = $value;
            }
        }
        return $data;
    }

    /**
     * @param array $address
     * @return mixed
     */
    protected function getRegionId($address)
    {
        $country = $this->countryFactory->create()->loadByCode($address['country_id']);
        return $country->getRegionCollection()->addFieldToFilter('name', $address['region'])->getFirstItem()->getId();
    }
}
