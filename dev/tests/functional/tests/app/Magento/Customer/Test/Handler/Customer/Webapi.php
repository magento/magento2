<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Handler\Customer;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Webapi as AbstractWebapi;

/**
 * Webapi handler for creating customer.
 */
class Webapi extends AbstractWebapi implements CustomerInterface
{
    /**
     * Default customer group.
     */
    const GENERAL_GROUP = '1';

    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'gender' => [
            'Male' => 1,
            'Female' => 2,
            'Not Specified' => 3
        ],
        'country_id' => [
            'United States' => 'US',
            'United Kingdom' => 'GB',
            'Germany' => 'DE'
        ],
        'region_id' => [
            'California' => 12,
            'New York' => 43,
            'Texas' => 57,
        ],
    ];

    /**
     * Create customer via Web API.
     *
     * @param FixtureInterface|null $customer
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $customer = null)
    {
        /** @var Customer $customer */
        $data = $this->prepareData($customer);
        $url = $_ENV['app_frontend_url'] . 'rest/V1/customers';

        $this->webapiTransport->write($url, $data);
        $response = json_decode($this->webapiTransport->read(), true);
        $this->webapiTransport->close();

        if (empty($response['id'])) {
            $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
            throw new \Exception('Customer creation by Web API handler was not successful!');
        }

        return ['id' => $response['id']];
    }

    /**
     * Prepare customer data for Web API.
     *
     * @param Customer $customer
     * @return array
     */
    protected function prepareData(Customer $customer)
    {
        $data['customer'] = $this->replaceMappingData($customer->getData());
        $data['customer']['group_id'] = $this->getCustomerGroup($customer);
        $data['password'] = $data['customer']['password'];
        $data['customer']['website_id'] = $this->getCustomerWebsite($customer);
        unset($data['customer']['password']);
        unset($data['customer']['password_confirmation']);
        $data = $this->prepareAddressData($data);

        return $data;
    }

    /**
     * Get customer group.
     *
     * @param Customer $customer
     * @return string
     */
    protected function getCustomerGroup(Customer $customer)
    {
        return $customer->hasData('group_id')
            ? $customer->getDataFieldConfig('group_id')['source']->getCustomerGroup()->getCustomerGroupId()
            : self::GENERAL_GROUP;
    }

    /**
     * Prepare address data for Web API.
     *
     * @param array $data
     * @return array
     */
    protected function prepareAddressData(array $data)
    {
        if (!isset($data['customer']['address'])) {
            return $data;
        }
        foreach ($data['customer']['address'] as $key => $addressData) {
            $addressData = $this->prepareRegionData($addressData);
            $addressData = $this->prepareStreetData($addressData);
            $addressData = $this->prepareDefaultAddressData($addressData);
            unset($addressData['email']);
            $data['customer']['addresses'][$key] = $addressData;
        }
        unset($data['customer']['address']);

        return $data;
    }

    /**
     * Prepare region data for the address.
     *
     * @param array $addressData
     * @return array
     */
    protected function prepareRegionData(array $addressData)
    {
        if (isset($addressData['region'])) {
            $addressData['region'] = [
                'region' => $addressData['region'],
            ];
        }
        if (isset($addressData['region_id'])) {
            $addressData['region'] = [
                'region_id' => $addressData['region_id']
            ];
            unset($addressData['region_id']);
        }

        return $addressData;
    }

    /**
     * Prepare street data for the address.
     *
     * @param array $addressData
     * @return array
     */
    protected function prepareStreetData(array $addressData)
    {
        if (!is_array($addressData['street'])) {
            $street[] = $addressData['street'];
            $addressData['street'] = $street;
        }

        return $addressData;
    }

    /**
     * Prepare default address data.
     *
     * @param array $addressData
     * @return array
     */
    protected function prepareDefaultAddressData(array $addressData)
    {
        if (isset($addressData['default_billing']) && $addressData['default_billing'] === 'Yes') {
            $addressData['default_billing'] = true;
        } else {
            $addressData['default_billing'] = false;
        }
        if (isset($addressData['default_shipping']) && $addressData['default_shipping'] === 'Yes') {
            $addressData['default_shipping'] = true;
        } else {
            $addressData['default_shipping'] = false;
        }

        return $addressData;
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
