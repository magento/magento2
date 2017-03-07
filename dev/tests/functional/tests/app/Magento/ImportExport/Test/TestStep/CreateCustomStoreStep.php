<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\TestStep;

use Magento\ImportExport\Test\Fixture\ImportData;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Create custom store step.
 */
class CreateCustomStoreStep implements TestStepInterface
{
    /**
     * Website code mapping.
     */
    private $codeMapping =[
        'base' => 'Main Website[USD]'
    ];

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
     * Currency.
     *
     * @var string
     */
    private $currency;

    /**
     * Import fixture.
     *
     * @var ImportData
     */
    private $import;

    /**
     * @param TestStepFactory $stepFactory
     * @param FixtureFactory $fixtureFactory
     * @param ImportData $import
     * @param string $currency
     */
    public function __construct(
        TestStepFactory $stepFactory,
        FixtureFactory $fixtureFactory,
        ImportData $import,
        $currency = 'EUR'
    ) {
        $this->stepFactory = $stepFactory;
        $this->fixtureFactory = $fixtureFactory;
        $this->import = $import;
        $this->currency = $currency;
    }

    /**
     * Fill import form.
     *
     * @return void
     */
    public function run()
    {
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => 'price_scope_website']
        )->run();

        $products = $this->import->getDataFieldConfig('import_file')['source']->getEntities();
        foreach ($products as $product) {
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
        $this->getCsv($products);
    }

    /**
     * Return refactored csv data with custom store.
     *
     * @param array $products
     * @return void
     */
    public function getCsv(array $products)
    {
        foreach ($products as $product) {
            $website = $product->getDataFieldConfig('website_ids')['source']->getWebsites()[0];
            $this->codeMapping[$website->getCode()] = $website->getName() . "[{$this->currency}]";
        }

        $csv = $this->import->getDataFieldConfig('import_file')['source']->getCsv();
        $this->import->getDataFieldConfig('import_file')['source']->setCsv(strtr($csv, $this->codeMapping));
    }
}
