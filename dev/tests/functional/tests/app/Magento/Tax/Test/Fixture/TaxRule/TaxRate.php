<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Fixture\TaxRule;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Class TaxRate
 *
 * Data keys:
 *  - dataset
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
        if (isset($data['dataset'])) {
            $datasets = $data['dataset'];
            foreach ($datasets as $dataset) {
                /** @var \Magento\Tax\Test\Fixture\TaxRate $taxRate */
                $taxRate = $fixtureFactory->createByCode('taxRate', ['dataset' => $dataset]);
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
