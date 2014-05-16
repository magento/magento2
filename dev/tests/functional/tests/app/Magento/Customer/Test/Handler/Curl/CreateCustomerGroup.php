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
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;
use Mtf\System\Config;

/**
 * Curl handler for creating customer group in admin
 *
 */
class CreateCustomerGroup extends Curl
{
    /**
     * Url for saving data
     *
     * @var string
     */
    protected $saveUrl = 'customer/group/save/';

    /**
     * Prepare POST data for creating customer request
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData(FixtureInterface $fixture)
    {
        $data = $fixture->getData('fields');
        foreach ($data as $key => $values) {
            $value = $this->getValue($values);
            if (null === $value) {
                continue;
            }
            $data[$key] = $value;
        }

        return $data;
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
     * @param FixtureInterface $fixture [optional]
     * @return mixed
     */
    public function persist(FixtureInterface $fixture = null)
    {
        /** @var \Magento\Customer\Test\Fixture\CustomerGroup $fixture*/
        $params = $this->prepareData($fixture);
        $url = $_ENV['app_backend_url'] . $this->saveUrl;
        $curl = new BackendDecorator(new CurlTransport(), new Config);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0', array(), $params);
        $response = $curl->read();
        $curl->close();

        return $this->findId($response, $fixture->getGroupName());
    }

    /**
     * Find id of new customer group in response
     *
     * @param $response
     * @param $name
     * @return string
     */
    protected function findId($response, $name)
    {
        $regExp = '~/customer/group/edit/id/(\d+)(?=.*?' . $name. ')~s';
        preg_match_all($regExp, $response, $matches);
        $result = '';
        if (!empty($matches[1])) {
            $result = array_pop($matches[1]);
        }
        return $result;
    }
}
