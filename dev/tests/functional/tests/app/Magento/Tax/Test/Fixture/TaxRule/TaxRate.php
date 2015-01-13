<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Fixture\TaxRule;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class TaxRate
 *
 * Data keys:
 *  - dataSet
 */
class TaxRate implements FixtureInterface
{
    /**
     * Array with tax rates codes
     *
     * @var array
     */
    protected $data;

    /**
     * Array with tax rate fixtures
     *
     * @var array
     */
    protected $fixture;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['dataSet'])) {
            $dataSets = $data['dataSet'];
            foreach ($dataSets as $dataSet) {
                if ($dataSet !== '-') {
                    /** @var \Magento\Tax\Test\Fixture\TaxRate $taxRate */
                    $taxRate = $fixtureFactory->createByCode('taxRate', ['dataSet' => $dataSet]);
                    $this->fixture[] = $taxRate;
                    $this->data[] = $taxRate->getCode();
                }
            }
        }
    }

    /**
     * Persist custom selections tax rates
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
     * Return tax rate fixtures
     *
     * @return array
     */
    public function getFixture()
    {
        return $this->fixture;
    }
}
