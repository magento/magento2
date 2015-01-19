<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Handler\CustomerInjectable;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Curl handler for creating customer through registration page.
 */
class Curl extends AbstractCurl implements CustomerInjectableInterface
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
            'United States' => 'US'
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
        'account' => [
            'group_id',
            'firstname',
            'lastname',
            'email',
            'dob',
            'taxvat',
            'gender'
        ]
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
        $address = [];
        $result = [];
        /** @var CustomerInjectable $customer */
        $url = $_ENV['app_frontend_url'] . 'customer/account/createpost/?nocookie=true';
        $data = $customer->getData();
        $data['group_id'] = $this->getCustomerGroup($customer);

        if ($customer->hasData('address')) {
            $address = $customer->getAddress();
            unset($data['address']);
        }

        $curl = new CurlTransport();
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();
        if (!strpos($response, 'data-ui-id="global-messages-message-success"')) {
            throw new \Exception("Customer entity creating  by curl handler was not successful! Response: $response");
        }

        $result['id'] = $this->getCustomerId($customer->getEmail());
        $data['customer_id'] = $result['id'];

        if (!empty($address)) {
            $data['address'] = $address;
            $this->addAddress($data);
        }

        return $result;
    }

    /**
     * Get customer id by email
     *
     * @param string $email
     * @return int|null
     */
    protected function getCustomerId($email)
    {
        $url = $_ENV['app_backend_url'] . 'customer/index/grid/filter/' . $this->encodeFilter(['email' => $email]);
        $curl = new BackendDecorator(new CurlTransport(), new Config());

        $curl->write(CurlInterface::GET, $url, '1.0');
        $response = $curl->read();
        $curl->close();

        preg_match('/data-column="entity_id"[^>]*>\s*([0-9]+)\s*</', $response, $match);
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
     * Add addresses in to customer account
     *
     * @param array $data
     * @return void
     * @throws \Exception
     */
    protected function addAddress(array $data)
    {
        $curlData = [];
        $url = $_ENV['app_backend_url'] . 'customer/index/save/id/' . $data['customer_id'];
        foreach ($data as $key => $value) {
            foreach ($this->curlMapping as $prefix => $prefixValues) {
                if (in_array($key, $prefixValues)) {
                    $curlData[$prefix][$key] = $value;
                    unset($data[$key]);
                }
            }
        }
        unset($data['password'], $data['password_confirmation']);

        $curlData = $this->replaceMappingData(array_merge($curlData, $data));
        $curlData = $this->prepareAddressData($curlData);

        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->write(CurlInterface::POST, $url, '1.0', [], $curlData);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception('Failed to assign an address to the customer!');
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
        foreach (array_keys($curlData['address']) as $key) {
            $curlData['address'][$key]['_deleted'] = '';
            $curlData['address'][$key]['region'] = '';
            if (!is_array($curlData['address'][$key]['street'])) {
                $street = $curlData['address'][$key]['street'];
                $curlData['address'][$key]['street'] = [];
                $curlData['address'][$key]['street'][] = $street;
            }
            $newKey = 'new_' . ($key);
            if (isset($curlData['address'][$key]['default_billing'])) {
                $value = $curlData['address'][$key]['default_billing'] === 'Yes' ? 'true' : 'false';
                $curlData['address'][$key]['default_billing'] = $value;
            }
            if (isset($curlData['address'][$key]['default_shipping'])) {
                $value = $curlData['address'][$key]['default_shipping'] === 'Yes' ? 'true' : 'false';
                $curlData['address'][$key]['default_shipping'] = $value;
            }
            $curlData['account']['customer_address'][$newKey] = $curlData['address'][$key];
        }
        unset($curlData['address']);

        return $curlData;
    }

    /**
     * Encoded filter parameters
     *
     * @param array $filter
     * @return string
     */
    protected function encodeFilter(array $filter)
    {
        $result = [];
        foreach ($filter as $name => $value) {
            $result[] = "{$name}={$value}";
        }
        $result = implode('&', $result);

        return base64_encode($result);
    }
}
