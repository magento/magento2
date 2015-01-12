<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Handler\TaxRule;

use Mtf\Fixture\FixtureInterface;
use Mtf\Handler\Curl as AbstractCurl;
use Mtf\System\Config;
use Mtf\Util\Protocol\CurlInterface;
use Mtf\Util\Protocol\CurlTransport;
use Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Curl handler for creating Tax Rule
 */
class Curl extends AbstractCurl implements TaxRuleInterface
{
    /**
     * Default Tax Class values
     *
     * @var array
     */
    protected $defaultTaxClasses = [
        'tax_customer_class' => 3, // Retail Customer
        'tax_product_class' => 2, // Taxable Goods
    ];

    /**
     * Post request for creating tax rule
     *
     * @param FixtureInterface $fixture
     * @return mixed|null
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->prepareData($fixture);

        $url = $_ENV['app_backend_url'] . 'tax/rule/save/?back=1';
        $curl = new BackendDecorator(new CurlTransport(), new Config());
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write(CurlInterface::POST, $url, '1.0', [], $data);
        $response = $curl->read();
        $curl->close();

        preg_match("~Location: [^\s]*\/rule\/(\d+)~", $response, $matches);
        $id = isset($matches[1]) ? $matches[1] : null;
        return ['id' => $id];
    }

    /**
     * Returns data for curl POST params
     *
     * @param FixtureInterface $fixture
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function prepareData($fixture)
    {
        $data = $fixture->getData();
        $fields = [
            'tax_rate',
            'tax_customer_class',
            'tax_product_class',
        ];

        foreach ($fields as $field) {
            if (!array_key_exists($field, $data)) {
                $data[$field][] = $this->defaultTaxClasses[$field];
                continue;
            }
            $fieldFixture = $fixture->getDataFieldConfig($field);
            $fieldFixture = $fieldFixture['source']->getFixture();
            foreach ($data[$field] as $key => $value) {
                $id = $fieldFixture[$key]->getId();
                if ($id === null) {
                    $fieldFixture[$key]->persist();
                    $id = $fieldFixture[$key]->getId();
                }
                $data[$field][$key] = $id;
            }
        }

        return $data;
    }
}
