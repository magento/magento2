<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

namespace Magento\CatalogRule\Test\TestStep;

use Mtf\Fixture\FixtureFactory;
use Mtf\TestStep\TestStepInterface;

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
