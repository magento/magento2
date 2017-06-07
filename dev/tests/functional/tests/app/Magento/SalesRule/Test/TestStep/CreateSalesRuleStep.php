<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Creating sales rule.
 */
class CreateSalesRuleStep implements TestStepInterface
{
    /**
     * Sales Rule coupon.
     *
     * @var string
     */
    protected $salesRule;

    /**
     * Factory for Fixture.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Delete all Sales Rule on backend.
     *
     * @var DeleteAllSalesRuleStep
     */
    protected $deleteAllSalesRule;

    /**
     * Preparing step properties.
     *
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param DeleteAllSalesRuleStep $deleteRule
     * @param string $salesRule
     */
    public function __construct(FixtureFactory $fixtureFactory, DeleteAllSalesRuleStep $deleteRule, $salesRule = null)
    {
        $this->fixtureFactory = $fixtureFactory;
        $this->salesRule = $salesRule;
        $this->deleteAllSalesRule = $deleteRule;
    }

    /**
     * Create sales rule.
     *
     * @return array
     */
    public function run()
    {
        $result['salesRule'] = null;
        if ($this->salesRule !== null) {
            $salesRule = $this->fixtureFactory->createByCode(
                'salesRule',
                ['dataset' => $this->salesRule]
            );
            $salesRule->persist();
            $result['salesRule'] = $salesRule;
        }

        return $result;
    }

    /**
     * Delete all sales rule.
     *
     * @return void
     */
    public function cleanup()
    {
        if ($this->salesRule !== null) {
            $this->deleteAllSalesRule->run();
        }
    }
}
