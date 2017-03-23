<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Handler\TaxRule;

use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Curl handler for creating Tax Rule.
 */
class Curl extends AbstractCurl implements TaxRuleInterface
{
    /**
     * Default Tax Class values.
     *
     * @var array
     */
    protected $defaultTaxClasses = [
        'tax_customer_class' => 3, // Retail Customer
        'tax_product_class' => 2, // Taxable Goods
    ];

    /**
     * Post request for creating tax rule.
     *
     * @param FixtureInterface $fixture
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        /** @var TaxRule $fixture */
        $data = $this->prepareData($fixture);

        $url = $_ENV['app_backend_url'] . 'tax/rule/save/?back=1';
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();

        if (!strpos($response, 'data-ui-id="messages-message-success"')) {
            $this->_eventManager->dispatchEvent(['curl_failed'], [$response]);
            throw new \Exception("Tax rate creation by curl handler was not successful!");
        }

        preg_match("~Location: [^\s]*\/rule\/(\d+)~", $response, $matches);
        $id = isset($matches[1]) ? $matches[1] : null;

        return ['id' => $id];
    }

    /**
     * Returns data for Web API params.
     *
     * @param TaxRule $fixture
     * @return array
     */
    protected function prepareData(TaxRule $fixture)
    {
        $data = $fixture->getData();
        $data = $this->prepareFieldData($fixture, $data, 'tax_rate');
        $data = $this->prepareFieldData($fixture, $data, 'tax_product_class');
        $data = $this->prepareFieldData($fixture, $data, 'tax_customer_class');

        return $data;
    }

    /**
     * Prepare tax rule field data using new field.
     *
     * @param TaxRule $fixture
     * @param array $data
     * @param string $fixtureField
     * @param string $newField
     * @return array
     */
    public function prepareFieldData(TaxRule $fixture, array $data, $fixtureField, $newField = null)
    {
        $newField = $newField === null ? $fixtureField : $newField;
        unset($data[$fixtureField]);

        if (!$fixture->hasData($fixtureField)) {
            $data[$newField][] = $this->defaultTaxClasses[$fixtureField];
        } else {
            foreach ($fixture->getDataFieldConfig($fixtureField)['source']->getFixture() as $taxField) {
                if (!$taxField->hasData('id')) {
                    $taxField->persist();
                }
                $data[$newField][] = $taxField->getId();
            }
        }

        return $data;
    }
}
