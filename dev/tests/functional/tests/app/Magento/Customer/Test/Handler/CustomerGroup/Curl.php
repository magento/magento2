<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Handler\CustomerGroup;

use Magento\Backend\Test\Handler\Extractor;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Curl handler for creating customer group.
 */
class Curl extends AbstractCurl implements CustomerGroupInterface
{
    /**
     * Url for saving data.
     *
     * @var string
     */
    protected $saveUrl = 'customer/group/save/';

    /**
     * POST request for creating Customer Group.
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
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            throw new \Exception(
                "Customer Group entity creating by curl handler was not successful! Response: $response"
            );
        }

        return ['customer_group_id' => $this->getCustomerGroupId($data)];
    }

    /**
     * Get id after creating Customer Group.
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
