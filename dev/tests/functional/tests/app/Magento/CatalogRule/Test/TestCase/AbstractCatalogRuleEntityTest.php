<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\TestCase;

use Magento\PageCache\Test\Page\Adminhtml\AdminCache;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleIndex;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleNew;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Parent class for CatalogRule tests.
 */
abstract class AbstractCatalogRuleEntityTest extends Injectable
{
    /**
     * Page CatalogRuleIndex.
     *
     * @var CatalogRuleIndex
     */
    protected $catalogRuleIndex;

    /**
     * Page CatalogRuleNew.
     *
     * @var CatalogRuleNew
     */
    protected $catalogRuleNew;

    /**
     * Page AdminCache.
     *
     * @var AdminCache
     */
    protected $adminCache;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Injection data.
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
     * Clear data after test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(\Magento\CatalogRule\Test\TestStep\DeleteAllCatalogRulesStep::class)->run();
    }
}
