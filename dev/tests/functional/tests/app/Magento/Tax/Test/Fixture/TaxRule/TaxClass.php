<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Fixture\TaxRule;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Class TaxClass
 *
 * Data keys:
 *  - dataset
 */
class TaxClass extends DataSource
{
    /**
     * Array with tax class fixtures.
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
                /** @var \Magento\Tax\Test\Fixture\TaxClass $taxClass */
                $taxClass = $fixtureFactory->createByCode('taxClass', ['dataset' => $dataset]);
                $this->fixture[] = $taxClass;
                $this->data[] = $taxClass->getClassName();
            }
        }
    }

    /**
     * Return tax class fixture.
     *
     * @return array
     */
    public function getFixture()
    {
        return $this->fixture;
    }
}
