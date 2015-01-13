<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Fixture\CatalogRule;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Conditions
 *
 * Data keys:
 *  - dataSet
 */
class Conditions implements FixtureInterface
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var \Mtf\Fixture\FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * @var array
     */
    protected $params;

    /**
     * Constructor for preparing conditions data from repository
     *
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param string $data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data)
    {
        preg_match('/\[(.*)\]/', $data, $matches);
        $conditionsArray = explode(",", $matches[1]);
        $value = array_shift($conditionsArray);
        $parts = explode('|', $value);

        foreach ($parts as $key => $value) {
            $parts[$key] = trim($value);
        }

        if ($parts[0] == 'Category') {
            $this->data['conditions']['1--1']['attribute'] = 'category_ids';
        } elseif ($parts[1] == 'AttributeSet') {
            $this->data['conditions']['1--1']['attribute'] = 'attribute_set_id';
        }

        if ($parts[1] == 'is') {
            $this->data['conditions']['1--1']['operator'] = '==';
        } else {
            $this->data['conditions']['1--1']['operator'] = '!=';
        }

        $this->data['conditions']['1--1']['type'] = 'Magento\CatalogRule\Model\Rule\Condition\Product';

        if (!empty($parts[2])) {
            $this->data['conditions']['1--1']['value'] = $parts[2];
        }
    }

    /**
     * Persist custom selections conditions
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
     * @param string|null $key
     * @return array|mixed
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
}
