<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Create custom store step.
 */
class CreateCustomStoreStep implements TestStepInterface
{
    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Products.
     *
     * @var array
     */
    private $products;

    /**
     * Currency.
     *
     * @var string
     */
    private $currency;

    /**
     * Csv data.
     *
     * @var array
     */
    private $csv;

    /**
     * @param TestStepFactory $stepFactory
     * @param FixtureFactory $fixtureFactory
     * @param array $products
     * @param array $csv
     * @param string $currency
     */
    public function __construct(
        TestStepFactory $stepFactory,
        FixtureFactory $fixtureFactory,
        array $products,
        array $csv,
        $currency = 'EUR'
    ) {
        $this->stepFactory = $stepFactory;
        $this->fixtureFactory = $fixtureFactory;
        $this->products = $products;
        $this->currency = $currency;
        $this->csv = $csv;
    }

    /**
     * Fill import form.
     *
     * @return array
     */
    public function run()
    {
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => 'price_scope_website']
        )->run();

        foreach ($this->products as $product) {
            $websites = $product->getDataFieldConfig('website_ids')['source']->getWebsites();

            $configFixture = $this->fixtureFactory->createByCode(
                'configData',
                [
                    'data' => [
                        'currency/options/allow' => [
                            'value' =>  [$this->currency]
                        ],
                        'currency/options/base' => [
                            'value' => $this->currency
                        ],
                        'currency/options/default' => [
                            'value' => $this->currency
                        ],
                        'scope' => [
                            'fixture' => $websites[0],
                            'scope_type' => 'website',
                            'website_id' => $websites[0]->getWebsiteId(),
                            'set_level' => 'website',
                        ]
                    ]
                ]
            );
            $configFixture->persist();
        }

        return $this->getCsv();
    }

    /**
     * Return refactored csv data with custom store.
     *
     * @return array
     */
    public function getCsv()
    {
        $mapping['base'] = 'Main Website[USD]';

        foreach ($this->products as $product) {
            $website = $product->getDataFieldConfig('website_ids')['source']->getWebsites()[0];
            $mapping[$website->getCode()] = $website->getName() . "[{$this->currency}]";
        }

        $csv = [];
        foreach ($this->csv as $row) {
            $row = array_map(
                function ($value) use ($mapping) {
                    return strtr($value, $mapping);
                },
                $row
            );
            $csv[] = $row;
        }
        return $csv;
    }
}
