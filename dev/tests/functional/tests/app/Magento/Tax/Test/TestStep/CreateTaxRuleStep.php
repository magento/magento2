<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\TestStep;

use Mtf\Fixture\FixtureFactory;
use Mtf\TestStep\TestStepInterface;

/**
 * Creating tax rule
 */
class CreateTaxRuleStep implements TestStepInterface
{
    /**
     * Tax Rule
     *
     * @var string
     */
    protected $taxRule;

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
     * @param string $taxRule
     */
    public function __construct(FixtureFactory $fixtureFactory, $taxRule)
    {
        $this->fixtureFactory = $fixtureFactory;
        $this->taxRule = $taxRule;
    }

    /**
     * Create tax rule
     *
     * @return array
     */
    public function run()
    {
        $result['taxRule'] = null;
        if ($this->taxRule != '-') {
            $taxRule = $this->fixtureFactory->createByCode(
                'taxRule',
                ['dataSet' => $this->taxRule]
            );
            $taxRule->persist();
            $result['taxRule'] = $taxRule;
        }

        return $result;
    }
}
