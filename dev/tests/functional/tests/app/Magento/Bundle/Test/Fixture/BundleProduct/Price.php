<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Bundle\Test\Fixture\BundleProduct;

use Mtf\Fixture\FixtureInterface;

/**
 * Class Price
 * Data keys:
 *  - preset (Price verification preset name)
 *  - value (Price value)
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
     * Current preset
     *
     * @var string
     */
    protected $currentPreset;

    /**
     * Constructor
     *
     * @constructor
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(array $params, array $data = [])
    {
        $this->params = $params;
        $this->data = (isset($data['value']) && $data['value'] != '-') ? $data['value'] : null;
        if (isset($data['preset'])) {
            $this->currentPreset = $data['preset'];
        }
    }

    /**
     * Persist fixture
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
     * @param string $key [optional]
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
     * Get preset array
     *
     * @return array|null
     */
    public function getPreset()
    {
        $presets = [
            'drop_down_with_one_option_fixed_price' => [
                'price_from' => '115.00',
                'price_to' => '120.00',
                'cart_price' => '145.00'
            ],
            'drop_down_with_one_option_percent_price' => [
                'price_from' => '115.00',
                'price_to' => '120.00',
                'cart_price' => '126.00'
            ],
            'MAGETWO-23070' => [
                'price_from' => '40.00',
                'price_to' => '100.00',
                'cart_price' => '100.00'
            ],
            'MAGETWO-23061' => [
                'price_from' => '32.00',
                'price_to' => '80.00',
                'cart_price' => '80.00'
            ],
            'dynamic-200' => [
                'price_from' => '200.00',
                'price_to' => '500.00',
                'cart_price' => '400.00'
            ],
            'fixed-24' => [
                'price_from' => '96.00',
                'price_to' => '97.00',
                'cart_price' => '244.00'
            ],
            'fixed-1' => [
                'price_from' => '1.00',
                'price_to' => '10.00',
                'cart_price' => '80.00'
            ],
            'dynamic-8' => [
                'price_from' => '8.00',
                'price_to' => '20.00',
                'cart_price' => '80.00'
            ],
            'dynamic-32' => [
                'price_from' => '32.00',
                'price_to' => '80.00',
                'cart_price' => '80.00'
            ],
            'dynamic-40' => [
                'price_from' => '40.00',
                'price_to' => '100.00',
                'cart_price' => '100.00'
            ],
            'dynamic-50' => [
                'price_from' => 'As low as $50.00',
            ],
            'fixed-115' => [
                'price_from' => '115.00',
                'price_to' => '120.00',
                'cart_price' => '317.00'
            ],
            'fixed-126' => [
                'price_from' => '115.00',
                'price_to' => '120.00',
                'cart_price' => '126.00'
            ],
            'fixed-15' => [
                'price_from' => '15.00',
                'price_to' => '16.00',
                'cart_price' => '80.00'
            ],
            'default_fixed' => [
                'compare_price' => [
                    'price_from' => '755.00',
                    'price_to' => '756.00'
                ],
            ],
            'default_dynamic' => [
                'compare_price' => [
                    'price_from' => '100.00',
                    'price_to' => '560.00'
                ],
            ],
            'dynamic-100' => [
                'price_from' => '100.00',
                'price_to' => '560.00',
                'cart_price' => '100.00'
            ],
            'fixed-756' => [
                'price_from' => '755.00',
                'price_to' => '756.00',
                'cart_price' => '756.00'
            ],
        ];
        if (!isset($presets[$this->currentPreset])) {
            return null;
        }
        return $presets[$this->currentPreset];
    }
}
