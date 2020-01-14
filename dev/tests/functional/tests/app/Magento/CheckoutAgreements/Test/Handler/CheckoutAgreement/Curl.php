<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\Handler\CheckoutAgreement;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Curl handler for creating Checkout Agreement
 */
class Curl extends AbstractCurl implements CheckoutAgreementInterface
{
    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'is_active' => [
            'Enabled' => 1,
            'Disabled' => 0,
        ],
        'is_html' => [
            'HTML' => 1,
            'Text' => 0,
        ],
    ];

    /**
     * Url for save checkout agreement
     *
     * @var string
     */
    protected $url = 'checkout/agreement/save/';

    /**
     * Post request for creating new checkout agreement
     *
     * @param FixtureInterface|null $fixture
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $url = $_ENV['app_backend_url'] . $this->url;
        $data = $this->prepareData($fixture);
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();
        if (strpos($response, 'data-ui-id="messages-message-success"') === false) {
            throw new \Exception("Checkout agreement creating by curl handler was not successful! Response: $response");
        }
        preg_match('~id\/(\d*?)\/~', $response, $matches);
        $id = isset($matches[1]) ? $matches[1] : null;

        return ['agreement_id' => $id];
    }

    /**
     * Prepare data
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData($fixture)
    {
        $data = [];
        /** @var \Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement $fixture */
        $stores = $fixture->getDataFieldConfig('stores')['source']->getStores();
        foreach ($stores as $store) {
            /** @var \Magento\Store\Test\Fixture\Store $store */
            $data['stores'][] = $store->getStoreId();
        }
        $data = $this->replaceMappingData(array_merge($fixture->getData(), $data));

        return $data;
    }
}
