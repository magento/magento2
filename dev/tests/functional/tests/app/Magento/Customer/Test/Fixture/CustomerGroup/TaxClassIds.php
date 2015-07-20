<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Fixture\CustomerGroup;

use Magento\Mtf\Fixture\DataSource;
use Magento\Tax\Test\Fixture\TaxClass;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Class TaxClassIds
 *
 * Data keys:
 *  - dataset
 */
class TaxClassIds extends DataSource
{
    /**
     * TaxClass fixture
     *
     * @var TaxClass
     */
    protected $taxClass;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        array $params,
        array $data
    ) {
        $this->params = $params;
        if (isset($data['dataset'])) {
            $dataset = $data['dataset'];
            /** @var \Magento\Tax\Test\Fixture\TaxClass $taxClass */
            $taxClass = $fixtureFactory->createByCode('taxClass', ['dataset' => $dataset]);
            if (!$taxClass->hasData('id')) {
                $taxClass->persist();
            }
            $this->data = $taxClass->getClassName();
            $this->taxClass = $taxClass;
        }
    }

    /**
     * Return TaxClass fixture
     *
     * @return TaxClass
     */
    public function getTaxClass()
    {
        return $this->taxClass;
    }
}
