<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\CatalogRule\Test\Fixture\CatalogRule;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Util\Command\Cli\Cron;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\Util\Command\Cli\Indexer;
use Magento\Indexer\Test\Constraint\AssertIndexerStatus;
use Magento\CatalogRule\Test\Constraint\AssertCatalogPriceRuleNotAppliedProductPage;
use Magento\CatalogRule\Test\Constraint\AssertCatalogPriceRuleAppliedProductPage;
use Magento\Indexer\Test\Page\Adminhtml\IndexManagement;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleIndex;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleNew;
use Magento\Catalog\Test\TestStep\CreateProductsStep;

/**
 * Catalog rules indexer test.
 *
 * Preconditions:
 * 1. Create several products and categories.
 * 2. All indexers are reindexed and in READY state.
 *
 * Steps:
 * 1. Create catalog rule.
 * 2. Call an assert AssertIndexerStatus.
 * 3. Call an assert AssertCatalogPriceRuleNotAppliedProductPage.
 * 4. Apply catalog rule.
 * 5. Run cron twice.
 * 6. Call an assert AssertIndexerStatus.
 * 7. Call an assert AssertCatalogPriceRuleAppliedProductPage.
 * 8. Update catalog rule for new discount.
 * 9. Call an assert AssertIndexerStatus.
 * 10. Call an assert AssertCatalogPriceRuleNotAppliedProductPage.
 * 11. Run cron twice.
 * 12. Call an assert AssertIndexerStatus.
 * 13. Call an assert AssertCatalogPriceRuleAppliedProductPageDelete.
 * 14. Delete catalog rule.
 * 15. Call an assert AssertIndexerStatus.
 * 16. Call an assert AssertCatalogPriceRuleAppliedProductPage.
 * 17. Run cron twice.
 * 18. Call an assert AssertIndexerStatus.
 * 19. Call an assert AssertCatalogPriceRuleNotAppliedProductPage.
 *
 * @ZephyrId MAGETWO-39072
 */
class CreateCatalogRulesIndexerTest extends Injectable
{
    /**
     * Catalog rule index page.
     *
     * @var CatalogRuleIndex
     */
    private $catalogRuleIndex;

    /**
     * New catalog rule page.
     *
     * @var CatalogRuleNew
     */
    private $catalogRuleNew;

    /**
     * Index management page.
     *
     * @var IndexManagement
     */
    private $indexManagement;

    /**
     * Cms index page.
     *
     * @var CmsIndex
     */
    private $cmsIndexPage;

    /**
     * Catalog product view page.
     *
     * @var CatalogProductView
     */
    private $catalogProductViewPage;

    /**
     * Catalog category view page.
     *
     * @var CatalogCategoryView
     */
    private $catalogCategoryViewPage;

    /**
     * Assert indexer status.
     *
     * @var AssertIndexerStatus
     */
    private $assertIndexerStatus;

    /**
     * Assert catalog price rule is not applied on product page.
     *
     * @var AssertCatalogPriceRuleNotAppliedProductPage
     */
    private $assertCatalogPriceRuleNotAppliedProductPage;

    /**
     * Assert catalog price rule applied on product page.
     *
     * @var AssertCatalogPriceRuleAppliedProductPage
     */
    private $assertCatalogPriceRuleAppliedProductPage;

    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Injection data.
     *
     * @param IndexManagement $indexManagement
     * @param CatalogRuleIndex $catalogRuleIndex
     * @param CatalogRuleNew $catalogRuleNew
     * @param CmsIndex $cmsIndexPage
     * @param CatalogProductView $catalogProductViewPage
     * @param CatalogCategoryView $catalogCategoryViewPage
     * @param AssertIndexerStatus $assertIndexerStatus
     * @param AssertCatalogPriceRuleNotAppliedProductPage $assertCatalogPriceRuleNotAppliedProductPage
     * @param AssertCatalogPriceRuleAppliedProductPage $assertCatalogPriceRuleAppliedProductPage
     * @param TestStepFactory $stepFactory
     * @return void
     */
    public function __inject(
        IndexManagement $indexManagement,
        CatalogRuleIndex $catalogRuleIndex,
        CatalogRuleNew $catalogRuleNew,
        CmsIndex $cmsIndexPage,
        CatalogProductView $catalogProductViewPage,
        CatalogCategoryView $catalogCategoryViewPage,
        AssertIndexerStatus $assertIndexerStatus,
        AssertCatalogPriceRuleNotAppliedProductPage $assertCatalogPriceRuleNotAppliedProductPage,
        AssertCatalogPriceRuleAppliedProductPage $assertCatalogPriceRuleAppliedProductPage,
        TestStepFactory $stepFactory
    ) {
        $this->indexManagement = $indexManagement;
        $this->catalogRuleIndex = $catalogRuleIndex;
        $this->catalogRuleNew = $catalogRuleNew;
        $this->cmsIndexPage = $cmsIndexPage;
        $this->catalogProductViewPage = $catalogProductViewPage;
        $this->catalogCategoryViewPage = $catalogCategoryViewPage;
        $this->assertIndexerStatus = $assertIndexerStatus;
        $this->assertCatalogPriceRuleNotAppliedProductPage = $assertCatalogPriceRuleNotAppliedProductPage;
        $this->assertCatalogPriceRuleAppliedProductPage = $assertCatalogPriceRuleAppliedProductPage;
        $this->stepFactory = $stepFactory;
    }

    /**
     * Catalog rules indexer test.
     *
     * @param Indexer $cli
     * @param CatalogRule $catalogPriceRule
     * @param CatalogRule $catalogPriceRuleOriginal
     * @param Cron $cron
     * @param array|null $productPrice1
     * @param array|null $productPrice2
     * @param Customer|null $customer
     * @param array|null $products
     * @param string|null $indexers
     * @return void
     */
    public function test(
        Indexer $cli,
        CatalogRule $catalogPriceRule,
        CatalogRule $catalogPriceRuleOriginal,
        Cron $cron,
        array $productPrice1 = null,
        array $productPrice2 = null,
        Customer $customer = null,
        array $products = null,
        $indexers = null
    ) {
        $products = $this->stepFactory->create(CreateProductsStep::class, ['products' => $products])->run()['products'];
        $cli->reindex();
        if ($customer !== null) {
            $customer->persist();
        }
        $catalogPriceRuleOriginal->persist();
        $this->assertIndexerStatus->processAssert($this->indexManagement, $indexers, true);
        $this->objectManager->create(\Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class)->run();
        $this->assertCatalogPriceRuleNotAppliedProductPage->processAssert(
            $this->catalogProductViewPage,
            $this->cmsIndexPage,
            $this->catalogCategoryViewPage,
            $products
        );
        $filter = [
            'name' => $catalogPriceRuleOriginal->getName(),
            'rule_id' => $catalogPriceRuleOriginal->getId(),
        ];
        $this->catalogRuleIndex->open();
        $this->catalogRuleIndex->getCatalogRuleGrid()->searchAndOpen($filter);
        $this->catalogRuleNew->getFormPageActions()->saveAndApply();
        $cron->run();
        $cron->run();
        $this->assertIndexerStatus->processAssert($this->indexManagement, $indexers, true);
        $this->assertCatalogPriceRuleAppliedProductPage->processAssert(
            $this->catalogProductViewPage,
            $this->cmsIndexPage,
            $this->catalogCategoryViewPage,
            $products,
            $productPrice1,
            $customer
        );
        $this->catalogRuleIndex->open();
        $this->catalogRuleIndex->getCatalogRuleGrid()->searchAndOpen($filter);
        $this->catalogRuleNew->getEditForm()->fill($catalogPriceRule);
        $this->catalogRuleNew->getFormPageActions()->saveAndApply();
        $this->assertIndexerStatus->processAssert($this->indexManagement, $indexers, false);
        $this->objectManager->create(\Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class)->run();
        $this->assertCatalogPriceRuleNotAppliedProductPage->processAssert(
            $this->catalogProductViewPage,
            $this->cmsIndexPage,
            $this->catalogCategoryViewPage,
            $products
        );
        $cron->run();
        $cron->run();
        $this->assertIndexerStatus->processAssert($this->indexManagement, $indexers, true);
        $this->assertCatalogPriceRuleAppliedProductPage->processAssert(
            $this->catalogProductViewPage,
            $this->cmsIndexPage,
            $this->catalogCategoryViewPage,
            $products,
            $productPrice2,
            $customer
        );
        $this->catalogRuleIndex->open();
        $this->catalogRuleIndex->getCatalogRuleGrid()->searchAndOpen($filter);
        $this->catalogRuleNew->getFormPageActions()->delete();
        $this->catalogRuleNew->getModalBlock()->acceptAlert();
        $this->assertIndexerStatus->processAssert($this->indexManagement, $indexers, false);
        $this->assertCatalogPriceRuleAppliedProductPage->processAssert(
            $this->catalogProductViewPage,
            $this->cmsIndexPage,
            $this->catalogCategoryViewPage,
            $products,
            $productPrice2,
            $customer
        );
        $cron->run();
        $cron->run();
        $this->assertIndexerStatus->processAssert($this->indexManagement, $indexers, true);
        $this->objectManager->create(\Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class)->run();
        $this->assertCatalogPriceRuleNotAppliedProductPage->processAssert(
            $this->catalogProductViewPage,
            $this->cmsIndexPage,
            $this->catalogCategoryViewPage,
            $products
        );
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
