<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Handler\TaxRate;

use Magento\Tax\Test\Fixture\TaxRate;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Curl handler for creating Tax Rate.
 */
class Curl extends AbstractCurl implements TaxRateInterface
{
    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'tax_country_id' => [
            'Australia' => 'AU',
            'United States' => 'US',
            'United Kingdom' => 'GB',
        ],
        'tax_region_id' => [
            '*' => '0',
            'California' => '12',
            'New York' => '43',
            'Texas' => '57',
        ],
        'zip_is_range' => [
            'Yes' => '1',
            'No' => '0'
        ]
    ];

    /**
     * Post request for creating tax rate.
     *
     * @param FixtureInterface $fixture [optional]
     * @return array
     */
    public function persist(FixtureInterface $fixture = null)
    {
        /** @var TaxRate $fixture */
        $data = $this->prepareData($fixture);

        $url = $_ENV['app_backend_url'] . 'tax/rate/ajaxSave/?isAjax=true';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();

        $id = $this->getTaxRateId($response);
        return ['id' => $id];
    }

    /**
     * Prepare tax rate data.
     *
     * @param TaxRate $taxRate
     * @return array
     */
    public function prepareData(TaxRate $taxRate)
    {
        return $this->replaceMappingData($taxRate->getData());
    }

    /**
     * Return saved tax rate id.
     *
     * @param $response
     * @return int|null
     * @throws \Exception
     */
    protected function getTaxRateId($response)
    {
        $data = json_decode($response, true);
        if ($data['success'] !== true) {
            throw new \Exception("Tax rate creation by curl handler was not successful! Response: $response");
        }
        return isset($data['tax_calculation_rate_id']) ? (int)$data['tax_calculation_rate_id'] : null;
    }
}
