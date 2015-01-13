<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\CatalogProductSimple;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class GroupPriceOptions
 *
 * Data keys:
 *  - preset (Price options preset name)
 *  - products (comma separated sku identifiers)
 */
class GroupPriceOptions implements FixtureInterface
{
    /**
     * @var \Mtf\Fixture\FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * @param array $params
     * @param array $data
     */
    public function __construct(array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['preset'])) {
            $this->data = $this->getPreset($data['preset']);
        }
    }

    /**
     * Persist group price
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set
     *
     * @param $key [optional]
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings
     *
     * @return string
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    protected function getPreset($name)
    {
        $presets = [
            'MAGETWO-23055' => [
                [
                    'price' => 90,
                    'website' => 'All Websites [USD]',
                    'customer_group' => 'NOT LOGGED IN',
                ],
            ],
            'MAGETWO-23061' => [
                [
                    'price' => 20,
                    'website' => 'All Websites [USD]',
                    'customer_group' => 'NOT LOGGED IN',
                ],
            ],
            'default' => [
                [
                    'price' => 20,
                    'website' => 'All Websites [USD]',
                    'customer_group' => 'NOT LOGGED IN',
                ],
            ],
            'tax_calculation' => [
                [
                    'price' => 90.99,
                    'website' => 'All Websites [USD]',
                    'customer_group' => 'General',
                ],
            ],
        ];
        if (!isset($presets[$name])) {
            return null;
        }
        return $presets[$name];
    }
}
