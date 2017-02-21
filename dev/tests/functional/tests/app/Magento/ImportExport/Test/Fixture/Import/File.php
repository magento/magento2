<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Fixture\Import;

use Magento\ImportExport\Mtf\Util\Import\File\CsvTemplate;
use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\Util\Generate\File\Generator;

/**
 * Fixture of file.
 */
class File extends DataSource
{
    /**
     * Fixture data.
     *
     * @var array
     */
    private $value;

    /**
     * Generator for the uploaded file.
     *
     * @var Generator
     */
    private $generator;

    /**
     * Factory for fixtures.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Products fixtures.
     *
     * @var FixtureInterface[]
     */
    private $products;

    /**
     * Object manager.
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @param ObjectManager $objectManager
     * @param FixtureFactory $fixtureFactory
     * @param Generator $generator
     * @param array $params
     * @param array|string $data
     */
    public function __construct(
        ObjectManager $objectManager,
        FixtureFactory $fixtureFactory,
        Generator $generator,
        array $params,
        $data = []
    ) {
        $this->params = $params;
        $this->value = $data;
        $this->generator = $generator;
        $this->fixtureFactory = $fixtureFactory;
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getData($key = null)
    {
        if (isset($this->data)) {
            return parent::getData($key);
        }

        $placeholders = [];
        if (!isset($this->products)
            && isset($this->value['products'])
            && is_array($this->value['products'])
        ) {
            $this->products = $this->createProducts();

            foreach ($this->products as $product) {
                $placeholders[] = ['%sku%' => $product->getData('sku')];
            }
        }

        if (isset($this->value['template']) && is_array($this->value['template'])) {
            $this->data = $this->generator->generate(
                $this->objectManager->create(
                    CsvTemplate::class,
                    [
                        'config' => array_merge(
                            $this->value['template'],
                            [
                                'placeholders' => $placeholders
                            ]
                        )
                    ]
                )
            );

            return parent::getData($key);
        }

        $filename = MTF_TESTS_PATH . $this->value;
        if (!file_exists($filename)) {
            throw new \Exception("CSV file '{$filename}'' not found on the server.");
        }

        $this->data = $filename;

        return parent::getData($key);
    }

    /**
     * Get products fixtures.
     *
     * @return FixtureInterface[]
     */
    public function getProducts()
    {
        return $this->products ?: [];
    }

    /**
     * Create products from configuration of variation.
     *
     * @return FixtureInterface[]
     */
    private function createProducts()
    {
        $products = [];
        foreach ($this->value['products'] as $key => $productDataSet) {
            list($fixtureCode, $dataset) = explode('::', $productDataSet);

            /** @var FixtureInterface[] $products */
            $products[$key] = $this->fixtureFactory->createByCode(trim($fixtureCode), ['dataset' => trim($dataset)]);
            if ($products[$key]->hasData('id') === false) {
                $products[$key]->persist();
            }
        }

        return $products;
    }
}
