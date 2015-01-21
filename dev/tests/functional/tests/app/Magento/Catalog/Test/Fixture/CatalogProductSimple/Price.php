<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\CatalogProductSimple;

use Mtf\Fixture\FixtureInterface;

/**
 * Class Price
 *
 * Data keys:
 *  - preset (Price verification preset name)
 *  - value (Price value)
 *
 */
class Price implements FixtureInterface
{
    /**
     * Prepared dataSet data
     *
     * @var array
     */
    protected $data;

    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params;

    /**
     * @var \Mtf\Fixture\FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * @var string
     */
    protected $currentPreset;

    /**
     * @param array $params
     * @param array $data
     */
    public function __construct(array $params, $data = [])
    {
        $this->params = $params;
        $this->data = (!isset($data['preset']) && !isset($data['value'])) ? $data : [];

        if (isset($data['value'])) {
            $this->data = $data['value'];
            if (is_array($this->data)) {
                $this->data = array_filter(
                    $this->data,
                    function ($value) {
                        return $value !== '-';
                    }
                );
            }
        }
        if (isset($data['preset'])) {
            $this->currentPreset = $data['preset'];
        }
    }

    /**
     * Persist custom selections products
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
     * @return array|null
     */
    public function getPreset()
    {
        $presets = [
            'drop_down_with_one_option_fixed_price' => [
                'category_price' => '100.00',
                'product_price' => '100.00',
                'cart_price' => '130.00',
            ],
            'drop_down_with_one_option_percent_price' => [
                'category_price' => '100.00',
                'product_price' => '100.00',
                'cart_price' => '140.00',
            ],
            'MAGETWO-23029' => [
                'category_price' => '100.00',
                'category_special_price' => '90.00',
                'product_price' => '100.00',
                'product_special_price' => '90.00',
                'cart_price' => '120.00',
            ],
            'MAGETWO-23030' => [
                'category_price' => '100.00',
                'category_special_price' => '90.00',
                'product_price' => '100.00',
                'product_special_price' => '90.00',
                'cart_price' => '126.00',
            ],
            'MAGETWO-23036' => [
                'category_price' => '100.00',
                'category_special_price' => '90.00',
                'product_price' => '100.00',
                'product_special_price' => '90.00',
                'cart_price' => '90.00',
            ],
        ];
        if (!isset($presets[$this->currentPreset])) {
            return null;
        }
        return $presets[$this->currentPreset];
    }
}
