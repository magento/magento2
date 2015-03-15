<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Fixture\TaxRule;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Class TaxClass
 *
 * Data keys:
 *  - dataSet
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
        if (isset($data['dataSet'])) {
            $dataSets = $data['dataSet'];
            foreach ($dataSets as $dataSet) {
                /** @var \Magento\Tax\Test\Fixture\TaxClass $taxClass */
                $taxClass = $fixtureFactory->createByCode('taxClass', ['dataSet' => $dataSet]);
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
