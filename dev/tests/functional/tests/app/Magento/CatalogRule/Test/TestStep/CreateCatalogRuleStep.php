<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Creating catalog rule
 */
class CreateCatalogRuleStep implements TestStepInterface
{
    /**
     * Catalog Rule dataset name
     *
     * @var string
     */
    protected $catalogRule;

    /**
     * Factory for Fixture
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Preparing step properties
     *
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param string $catalogRule
     */
    public function __construct(FixtureFactory $fixtureFactory, $catalogRule)
    {
        $this->fixtureFactory = $fixtureFactory;
        $this->catalogRule = $catalogRule;
    }

    /**
     * Create catalog rule
     *
     * @return array
     */
    public function run()
    {
        $result['catalogRule'] = null;
        if ($this->catalogRule != '-') {
            $catalogRule = $this->fixtureFactory->createByCode(
                'catalogRule',
                ['dataSet' => $this->catalogRule]
            );
            $catalogRule->persist();
            $result['catalogRule'] = $catalogRule;
        }
        return $result;
    }
}
