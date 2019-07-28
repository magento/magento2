<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Handler\Customer;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Curl handler for creating customer through registration page.
 */
class Curl extends AbstractCurl implements CustomerInterface
{
    /**
     * Default customer group
     */
    const GENERAL_GROUP = '1';

    /**
     * Mapping values for data
     *
     * @var array
     */
    protected $mappingData = [
        'country_id' => [
            'United States' => 'US',
            'United Kingdom' => 'GB',
            'Germany' => 'DE'
        ],
        'gender' => [
            'Male' => 1,
            'Female' => 2,
            'Not Specified' => 3
        ],
        'region_id' => [
            'California' => 12,
            'New York' => 43,
            'Texas' => 57,
        ],
    ];

    /**
     * Curl mapping data
     *
     * @var array
     */
    protected $curlMapping = [
        'customer' => [
            'group_id',
            'firstname',
            'lastname',
            'email',
            'dob',
            'taxvat',
            'gender',
            'entity_id',
        ]
    ];

    /**
     * Fields that have to be send using update curl.
     *
     * @var array
     */
    protected $fieldsToUpdate = [
        'address',
        'group_id',
    ];

    /**
     * Post request for creating customer in frontend
     *
     * @param FixtureInterface|null $customer
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $customer = null)
    {
        /** @var Customer $customer */
        $data = $customer->getData();
        $data['group_id'] = $this->getCustomerGroup($customer);
        $data['website_id'] = $this->getCustomerWebsite($customer);
        $address = [];
        $url = $_ENV['app_frontend_url'] . 'customer/account/createpost/?nocookie=true';

        if ($customer->hasData('address')) {
            $address = $customer->getAddress();
            unset($data['address']);
        }

        $curl = new CurlTransport();
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();
        // After caching My Account page we cannot check by success message
        if (strpos($response, 'block-dashboard-info') === false) {
            throw new \Exception("Customer entity creating  by curl handler was not successful! Response: $response");
        }

        $data['entity_id'] = $this->getCustomerId($customer->getEmail());

        if (!empty($address)) {
            $data['address'] = $address;
        }
        $this->updateCustomer($data);

        return ['id' => $data['entity_id']];
    }

    /**
     * Get customer id by email
     *
     * @param string $email
     * @return int|null
     */
    protected function getCustomerId($email)
    {
        $url = $_ENV['app_backend_url'] . 'mui/index/render/';
        $data = [
            'namespace' => 'customer_listing',
            'filters' => [
                'placeholder' => true,
                'email' => $email
            ],
            'isAjax' => true
        ];
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);

        $curl->write($url, $data, CurlInterface::POST);
        $response = $curl->read();
        $curl->close();

        preg_match('/customer_listing_data_source.+items.+"entity_id":"(\d+)"/', $response, $match);
        return empty($match[1]) ? null : $match[1];
    }

    /**
     * Prepare customer for curl
     *
     * @param FixtureInterface $customer
     * @return string
     */
    protected function getCustomerGroup(FixtureInterface $customer)
    {
        return $customer->hasData('group_id')
            ? $customer->getDataFieldConfig('group_id')['source']->getCustomerGroup()->getCustomerGroupId()
            : self::GENERAL_GROUP;
    }

    /**
     * Update customer fields that can not be added at creation step.
     * - address
     * - group_id
     *
     * @param array $data
     * @return void
     * @throws \Exception
     */
    protected function updateCustomer(array $data)
    {
        $result = array_intersect($this->fieldsToUpdate, array_keys($data));
        if (empty($result)) {
            return;
        }
        $curlData = [];
        foreach ($data as $key => $value) {
            foreach ($this->curlMapping as $prefix => $prefixValues) {
                if (in_array($key, $prefixValues)) {
                    $curlData[$prefix][$key] = $value;
                    unset($data[$key]);
                }
            }
        }
        unset($data['password'], $data['password_confirmation']);

        $curlData = $this->replaceMappingData(array_replace_recursive($curlData, $data));
        if (!empty($data['address'])) {
            $curlData = $this->prepareAddressData($curlData);
        }

        $url = $_ENV['app_backend_url'] . 'customer/index/save/id/' . $curlData['customer']['entity_id'];
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, $curlData);
        $response = $curl->read();
        $curl->close();

        if (strpos($response, 'data-ui-id="messages-message-success"') === false) {
            $this->_eventManager->dispatchEvent(['curl_failed'], [$response]);
            throw new \Exception('Failed to update customer!');
        }
    }

    /**
     * Preparing address data for curl
     *
     * @param array $curlData
     * @return array
     */
    protected function prepareAddressData(array $curlData)
    {
        $address = [];
        foreach (array_keys($curlData['address']) as $key) {
            $addressKey = 'new_' . $key;
            $address[$addressKey] = $curlData['address'][$key];
            $address[$addressKey]['_deleted'] = '';
            $address[$addressKey]['region'] = '';
            if (!is_array($address[$addressKey]['street'])) {
                $street = $address[$addressKey]['street'];
                $address[$addressKey]['street'] = [];
                $address[$addressKey]['street'][] = $street;
            }
            if (isset($address[$addressKey]['default_billing'])) {
                $value = $address[$addressKey]['default_billing'] === 'Yes' ? 'true' : 'false';
                $address[$addressKey]['default_billing'] = $value;
            }
            if (isset($address[$addressKey]['default_shipping'])) {
                $value = $address[$addressKey]['default_shipping'] === 'Yes' ? 'true' : 'false';
                $address[$addressKey]['default_shipping'] = $value;
            }
        }
        $curlData['address'] = $address;

        return $curlData;
    }

    /**
     * Prepare customer website data.
     *
     * @param Customer $customer
     * @return int
     */
    private function getCustomerWebsite(Customer $customer)
    {
        return $customer->getDataFieldConfig('website_id')['source']->getWebsite()->getWebsiteId();
    }
}
