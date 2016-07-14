<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * Delete all Tax Rule on backend.
     *
     * @var DeleteAllTaxRulesStep
     */
    protected $deleteAllTaxRule;

    /**
     * Preparing step properties.
     *
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param DeleteAllTaxRulesStep $deleteTaxRule
     * @param string $taxRule
     */
    public function __construct(FixtureFactory $fixtureFactory, DeleteAllTaxRulesStep $deleteTaxRule, $taxRule = null)
    {
        $this->fixtureFactory = $fixtureFactory;
        $this->taxRule = $taxRule;
        $this->deleteAllTaxRule = $deleteTaxRule;
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
                    ['dataset' => $taxRuleDataSet]
                );
                $taxRule->persist();
                $result['taxRule'] = $taxRule;
            }
        }

        return $result;
    }

    /**
     * Delete all tax rule.
     *
     * @return void
     */
    public function cleanup()
    {
        if ($this->taxRule !== null) {
            $this->deleteAllTaxRule->run();
        }
    }
}
