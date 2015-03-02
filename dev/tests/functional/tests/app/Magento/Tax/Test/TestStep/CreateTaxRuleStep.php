<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Creating tax rule.
 */
class CreateTaxRuleStep implements TestStepInterface
{
    /**
     * Tax Rule.
     *
     * @var string
     */
    protected $taxRule;

    /**
     * Factory for Fixture.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Preparing step properties.
     *
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param string $taxRule
     */
    public function __construct(FixtureFactory $fixtureFactory, $taxRule = null)
    {
        $this->fixtureFactory = $fixtureFactory;
        $this->taxRule = $taxRule;
    }

    /**
     * Create tax rule.
     *
     * @return array
     */
    public function run()
    {
        $result['taxRule'] = null;
        if ($this->taxRule !== null) {
            $taxRuleDataSets = explode(',', $this->taxRule);
            foreach ($taxRuleDataSets as $taxRuleDataSet) {
                $taxRule = $this->fixtureFactory->createByCode(
                    'taxRule',
                    ['dataSet' => $taxRuleDataSet]
                );
                $taxRule->persist();
                $result['taxRule'] = $taxRule;
            }
        }

        return $result;
    }
}
