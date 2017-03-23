<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\Product;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Repository\RepositoryFactory;

/**
 * Custom options fixture.
 *
 * Data keys:
 *  - dataset (Custom options dataset name)
 *  - import_products (comma separated data set name)
 */
class CustomOptions extends DataSource
{
    /**
     * Custom options data.
     *
     * @var array
     */
    protected $customOptions;

    /**
     * @constructor
     * @param RepositoryFactory $repositoryFactory
     * @param FixtureFactory|null $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(
        RepositoryFactory $repositoryFactory,
        FixtureFactory $fixtureFactory,
        array $params,
        array $data
    ) {
        $this->params = $params;
        $this->data = (!isset($data['dataset']) && !isset($data['import_products'])) ? $data : [];
        $this->customOptions = $this->data;

        if (isset($data['dataset']) && isset($this->params['repository'])) {
            $this->data = $repositoryFactory->get($this->params['repository'])->get($data['dataset']);
            $this->data = $this->replaceData($this->data, mt_rand());
            $this->customOptions = $this->data;
        }
        if (isset($data['import_products'])) {
            $importData = explode(',', $data['import_products']);
            $importCustomOptions = [];
            $importProducts = [];
            foreach ($importData as $item) {
                list($fixture, $dataset) = explode('::', $item);
                $product = $fixtureFactory->createByCode($fixture, ['dataset' => $dataset]);
                if ($product->hasData('id') !== null) {
                    $product->persist();
                }
                $importCustomOptions = array_merge($importCustomOptions, $product->getCustomOptions());
                $importProducts[] = $product->getSku();
            }
            $this->customOptions = array_merge($this->data, $importCustomOptions);
            $this->data['import'] = ['options' => $importCustomOptions, 'products' => $importProducts];
        }
    }

    /**
     * Replace custom options data.
     *
     * @param array $data
     * @param int $replace
     * @return array
     */
    protected function replaceData(array $data, $replace)
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->replaceData($value, $replace);
            }
            $result[$key] = str_replace('%isolation%', $replace, $value);
        }

        return $result;
    }

    /**
     * Return all custom options.
     *
     * @return array
     */
    public function getCustomOptions()
    {
        return $this->customOptions;
    }
}
