<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Creating catalog rule.
 */
class CreateCatalogRuleStep implements TestStepInterface
{
    /**
     * Catalog Rule dataset name.
     *
     * @var string
     */
    protected $catalogRule;

    /**
     * Factory for Fixture.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Delete all catalog rules step.
     *
     * @var $deleteAllCatalogRule
     */
    protected $deleteAllCatalogRule;

    /**
     * Preparing step properties.
     *
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param string $catalogRule
     * @param DeleteAllCatalogRulesStep $deleteRule
     */
    public function __construct(FixtureFactory $fixtureFactory, DeleteAllCatalogRulesStep $deleteRule, $catalogRule)
    {
        $this->fixtureFactory = $fixtureFactory;
        $this->catalogRule = $catalogRule;
        $this->deleteAllCatalogRule = $deleteRule;
    }

    /**
     * Create catalog rule.
     *
     * @return array
     */
    public function run()
    {
        $result['catalogRule'] = null;
        if ($this->catalogRule != '-') {
            $catalogRule = $this->fixtureFactory->createByCode(
                'catalogRule',
                ['dataset' => $this->catalogRule]
            );
            $catalogRule->persist();
            $result['catalogRule'] = $catalogRule;
        }
        return $result;
    }

    /**
     * Delete all catalog rule.
     *
     * @return void
     */
    public function cleanup()
    {
        if ($this->catalogRule != '-') {
            $this->deleteAllCatalogRule->run();
        }
    }
}
