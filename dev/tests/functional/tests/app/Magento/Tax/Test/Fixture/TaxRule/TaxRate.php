<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Fixture\TaxRule;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Class TaxRate
 *
 * Data keys:
 *  - dataSet
 */
class TaxRate extends DataSource
{
    /**
     * Array with tax rate fixtures.
     *
     * @var array
     */
    protected $fixture;

    /**
     * @constructor
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
                /** @var \Magento\Tax\Test\Fixture\TaxRate $taxRate */
                $taxRate = $fixtureFactory->createByCode('taxRate', ['dataSet' => $dataSet]);
                $this->fixture[] = $taxRate;
                $this->data[] = $taxRate->getCode();
            }
        }
    }

    /**
     * Return tax rate fixtures.
     *
     * @return array
     */
    public function getFixture()
    {
        return $this->fixture;
    }
}
