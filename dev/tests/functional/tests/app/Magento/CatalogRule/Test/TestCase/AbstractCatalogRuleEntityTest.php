<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\TestCase;

use Magento\Backend\Test\Page\Adminhtml\AdminCache;
use Magento\CatalogRule\Test\Fixture\CatalogRule;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleIndex;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleNew;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestCase\Injectable;

/**
 * Parent class for CatalogRule tests
 */
abstract class AbstractCatalogRuleEntityTest extends Injectable
{
    /**
     * Page CatalogRuleIndex
     *
     * @var CatalogRuleIndex
     */
    protected $catalogRuleIndex;

    /**
     * Page CatalogRuleNew
     *
     * @var CatalogRuleNew
     */
    protected $catalogRuleNew;

    /**
     * Page AdminCache
     *
     * @var AdminCache
     */
    protected $adminCache;

    /**
     * Fixture CatalogRule
     *
     * @var array
     */
    protected $catalogRules = [];

    /**
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Injection data
     *
     * @param CatalogRuleIndex $catalogRuleIndex
     * @param CatalogRuleNew $catalogRuleNew
     * @param AdminCache $adminCache
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        CatalogRuleIndex $catalogRuleIndex,
        CatalogRuleNew $catalogRuleNew,
        AdminCache $adminCache,
        FixtureFactory $fixtureFactory
    ) {
        $this->catalogRuleIndex = $catalogRuleIndex;
        $this->catalogRuleNew = $catalogRuleNew;
        $this->adminCache = $adminCache;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Clear data after test
     *
     * @return void
     */
    public function tearDown()
    {
        foreach ($this->catalogRules as $catalogRule) {
            $filter = ['name' => $catalogRule->getName()];
            $this->catalogRuleIndex->open();
            $this->catalogRuleIndex->getCatalogRuleGrid()->searchAndOpen($filter);
            $this->catalogRuleNew->getFormPageActions()->delete();
        }
        $this->catalogRules = [];
    }
}
