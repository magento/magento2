<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Handler\CustomerGroupInjectable;

use Magento\Backend\Test\Handler\Extractor;
use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Curl handler for creating customer group
 */
class Curl extends AbstractCurl implements CustomerGroupInjectableInterface
{
    /**
     * Url for saving data
     *
     * @var string
     */
    protected $saveUrl = 'customer/group/save/';

    /**
     * POST request for creating Customer Group
     *
     * @param FixtureInterface $fixture
     * @return array|mixed
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data['code'] = $fixture->getCustomerGroupCode();
        $data['tax_class'] = $fixture->getDataFieldConfig('tax_class_id')['source']->getTaxClass()->getId();
        $url = $_ENV['app_backend_url'] . $this->saveUrl;
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception(
                "Customer Group entity creating by curl handler was not successful! Response: $response"
            );
        }

        return ['customer_group_id' => $this->getCustomerGroupId($data, $response)];
    }

    /**
     * Get id after creating Customer Group
     *
     * @param array $data
     * @return int|null
     */
    public function getCustomerGroupId(array $data)
    {
        $url = 'customer/group/index/sort/time/dir/desc';
        $regExp = '/.*id\/(\d+)\/.*' . $data['code'] . '/siu';
        $extractor = new Extractor($url, $regExp);
        $match = $extractor->getData();

        return empty($match[1]) ? null : $match[1];
    }
}
