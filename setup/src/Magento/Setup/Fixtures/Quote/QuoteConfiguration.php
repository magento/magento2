<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures\Quote;

use Magento\Setup\Fixtures\FixtureModel;

/**
 * Configuration for generating quotes for orders.
 */
class QuoteConfiguration extends \Magento\Framework\DataObject
{
    /**
     * Product type for "big" configurable products.
     *
     * @var string
     */
    const BIG_CONFIGURABLE_TYPE = 'big_configurable';

    /**
     * Default value for minimum items (simple) per order configuration.
     *
     * @var int
     */
    const SIMPLE_PRODUCT_COUNT_FROM = 2;

    /**
     * Default value for maximum items (simple) per order configuration.
     *
     * @var int
     */
    const SIMPLE_PRODUCT_COUNT_TO = 2;

    /**
     * Default value for minimum items (configurable) per order configuration.
     *
     * @var int
     */
    const CONFIGURABLE_PRODUCT_COUNT_FROM = 0;

    /**
     * Default value for maximum items (configurable) per order configuration.
     *
     * @var int
     */
    const CONFIGURABLE_PRODUCT_COUNT_TO = 0;

    /**
     * Default value for minimum items (big configurable) per order configuration.
     *
     * @var int
     */
    const BIG_CONFIGURABLE_PRODUCT_COUNT_FROM = 0;

    /**
     * Default value for maximum items (big configurable) per order configuration.
     *
     * @var int
     */
    const BIG_CONFIGURABLE_PRODUCT_COUNT_TO = 0;

    /**
     * Mappings for number of different types of products in quote.
     *
     * @var array
     */
    protected $_globalMap = [
        'order_simple_product_count_to' => 'simple_count_to',
        'order_simple_product_count_from' => 'simple_count_from',
        'order_configurable_product_count_to' => 'configurable_count_to',
        'order_configurable_product_count_from' => 'configurable_count_from',
        'order_big_configurable_product_count_to' => 'big_configurable_count_to',
        'order_big_configurable_product_count_from' => 'big_configurable_count_from',
        'order_quotes_enable' => 'order_quotes_enable',
    ];

    /**
     * @var string
     */
    protected $fixtureDataFilename = 'orders_fixture_data.json';

    /**
     * @var FixtureModel
     */
    private $fixtureModel;

    /**
     * @param FixtureModel $fixtureModel
     */
    public function __construct(FixtureModel $fixtureModel)
    {
        $this->fixtureModel = $fixtureModel;
    }

    /**
     * Fills object with data.
     *
     * @return $this
     */
    public function load()
    {
        $this->addData([
            'simple_count_to' => self::SIMPLE_PRODUCT_COUNT_TO,
            'simple_count_from' => self::SIMPLE_PRODUCT_COUNT_FROM,
            'configurable_count_to' => self::CONFIGURABLE_PRODUCT_COUNT_TO,
            'configurable_count_from' => self::CONFIGURABLE_PRODUCT_COUNT_FROM,
            'big_configurable_count_to' => self::BIG_CONFIGURABLE_PRODUCT_COUNT_TO,
            'big_configurable_count_from' => self::BIG_CONFIGURABLE_PRODUCT_COUNT_FROM,
        ]);

        $this->setData(
            'fixture_data_filename',
            dirname(__DIR__) . DIRECTORY_SEPARATOR . "_files" . DIRECTORY_SEPARATOR . $this->fixtureDataFilename
        );
        $this->accumulateData();

        return $this;
    }

    /**
     * Accumulate data from fixute model to object values.
     *
     * @return $this
     */
    private function accumulateData()
    {
        foreach ($this->_globalMap as $getKey => $setKey) {
            $value = $this->fixtureModel->getValue($getKey);
            if (null !== $value) {
                $this->setData($setKey, $value);
            }
        }
        return $this;
    }
}
