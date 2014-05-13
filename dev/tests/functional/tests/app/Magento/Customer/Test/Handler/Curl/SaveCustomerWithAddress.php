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

namespace Magento\Customer\Test\Handler\Curl;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\System\Config;

/**
 * Curl handler for saving customer address in admin
 *
 */
class SaveCustomerWithAddress extends Curl
{
    /**
     * Url for saving data
     *
     * @var string
     */
    protected $saveUrl = '/customer/address/formPost/?nocookie=true';

    /**
     * Url for saving customer
     *
     * @var string
     */
    protected $saveCustomer = 'customer/account/createpost/?nocookie=true';

    /**
     * Url of new address form
     *
     * @var string
     */
    protected $addressNew = '/customer/address/new/?nocookie=true';

    /**
     * Form key
     *
     * @var string
     */
    protected $formKey;

    /**
     * Prepare POST data for creating customer request
     *
     * @param array $data
     * @return array
     */
    protected function prepareData($data)
    {
        $curlData = array();
        foreach ($data as $key => $values) {
            $value = $this->getValue($values);
            if (null === $value) {
                continue;
            }
            $curlData[$key] = $value;
        }
        $curlData['success_url'] = '';
        $curlData['error_url'] = '';
        $curlData['default_billing'] = 1;
        $curlData['default_shipping'] = 1;

        return $curlData;
    }

    /**
     * Retrieve field value or return null if value does not exist
     *
     * @param array $values
     * @return null|mixed
     */
    protected function getValue($values)
    {
        if (!isset($values['value'])) {
            return null;
        }
        return isset($values['input_value']) ? $values['input_value'] : $values['value'];
    }

    /**
     * Execute handler
     *
     * @param FixtureInterface $fixture
     * @return mixed
     */
    public function persist(FixtureInterface $fixture = null)
    {
        /** @var \Magento\Customer\Test\Fixture\Customer $fixture */
        $address = $fixture->getDefaultBillingAddress();
        $fields = $this->prepareData($address->getData('fields'));
        $url = $_ENV['app_frontend_url'] . $this->saveUrl;
        $curl = $this->saveCustomer($fixture);
        $fields['form_key'] = $this->formKey;
        $curl->write(CurlInterface::POST, $url, '1.0', array(), $fields);
        $response = $curl->read();
        $curl->close();

        return $response;
    }

    /**
     * Get from key from response
     *
     * @param string $response
     * @return string
     */
    protected function getFromKey($response)
    {
        preg_match('/input name="form_key" type="hidden" value="(\w+)"/', $response, $matches);
        $formKey = '';
        if (!empty($matches[1])) {
            $formKey = $matches[1];
        }
        return $formKey;
    }

    /**
     * Save new customer and get form key
     *
     * @param \Magento\Customer\Test\Fixture\Customer $fixture
     * @return CurlTransport
     */
    protected function saveCustomer(\Magento\Customer\Test\Fixture\Customer $fixture)
    {
        $data = $fixture->getData('fields');
        $fields = array();
        foreach ($data as $key => $field) {
            $fields[$key] = $field['value'];
        }
        $url = $_ENV['app_frontend_url'] . $this->saveCustomer;
        $curl = new CurlTransport();
        $curl->write(CurlInterface::POST, $url, '1.0', array(), $fields);
        $curl->read();
        $urlForm = $_ENV['app_frontend_url'] . $this->addressNew;
        $curl->write(CurlInterface::GET, $urlForm, '1.0', array());
        $response = $curl->read();
        $this->formKey = $this->getFromKey($response);

        return $curl;
    }
}
