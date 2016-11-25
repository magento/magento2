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
use Magento\CatalogRule\Test\TestCase\AbstractCatalogRuleEntityTest;


/**
 * Catalog rules indexer test.
 *
 * @ZephyrId MAGETWO-39072
 */
class CreateCatalogRulesIndexerTest extends AbstractCatalogRuleEntityTest
{
    /**
     * Catalog rules indexer test.
     *
     * @param Indexer $cli
     * @param IndexManagement $indexManagement
     * @param CmsIndex $cmsIndexPage
     * @param CatalogProductView $catalogProductViewPage
     * @param CatalogCategoryView $catalogCategoryViewPage
     * @param AssertIndexerStatus $assertIndexerStatus
     * @param AssertCatalogPriceRuleNotAppliedProductPage $assertCatalogPriceRuleNotAppliedProductPage
     * @param AssertCatalogPriceRuleAppliedProductPage $assertCatalogPriceRuleAppliedProductPage
     * @param TestStepFactory $stepFactory
     * @param CatalogRule $catalogPriceRule
     * @param CatalogRule $catalogPriceRuleOriginal
     * @param Cron $cron
     * @param array|null $productPrice1
     * @param array|null $productPrice2
     * @param Customer|null $customer
     * @param array|null $products
     * @param string|null $indexers
     * @return array
     */
    public function test(
        Indexer $cli,
        IndexManagement $indexManagement,
        CmsIndex $cmsIndexPage,
        CatalogProductView $catalogProductViewPage,
        CatalogCategoryView $catalogCategoryViewPage,
        AssertIndexerStatus $assertIndexerStatus,
        AssertCatalogPriceRuleNotAppliedProductPage $assertCatalogPriceRuleNotAppliedProductPage,
        AssertCatalogPriceRuleAppliedProductPage $assertCatalogPriceRuleAppliedProductPage,
        TestStepFactory $stepFactory,
        CatalogRule $catalogPriceRule,
        CatalogRule $catalogPriceRuleOriginal,
        Cron $cron,
        array $productPrice1 = null,
        array $productPrice2 = null,
        Customer $customer = null,
        array $products = null,
        $indexers = null
    ) {
        $products = $stepFactory->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $products]
        )->run()['products'];
        $cli->reindex();
        if ($customer !== null) {
            $customer->persist();
        }
        $catalogPriceRuleOriginal->persist();
        $assertIndexerStatus->processAssert($indexManagement, $indexers, true);
        $assertCatalogPriceRuleNotAppliedProductPage
            ->processAssert($catalogProductViewPage, $cmsIndexPage, $catalogCategoryViewPage, $products);
        $filter = [
            'name' => $catalogPriceRuleOriginal->getName(),
            'rule_id' => $catalogPriceRuleOriginal->getId(),
        ];
        $this->catalogRuleIndex->open();
        $this->catalogRuleIndex->getCatalogRuleGrid()->searchAndOpen($filter);
        $this->catalogRuleNew->getFormPageActions()->saveAndApply();
        $cron->run();
        $cron->run();
        $assertIndexerStatus->processAssert($indexManagement, $indexers, true);
        $assertCatalogPriceRuleAppliedProductPage
            ->processAssert($catalogProductViewPage, $cmsIndexPage, $catalogCategoryViewPage, $products, $productPrice1, $customer);
        $this->catalogRuleIndex->open();
        $this->catalogRuleIndex->getCatalogRuleGrid()->searchAndOpen($filter);
        $this->catalogRuleNew->getEditForm()->fill($catalogPriceRule);
        $this->catalogRuleNew->getFormPageActions()->saveAndApply();
        $assertIndexerStatus->processAssert($indexManagement, $indexers, false);
        $assertCatalogPriceRuleNotAppliedProductPage
            ->processAssert($catalogProductViewPage, $cmsIndexPage, $catalogCategoryViewPage, $products);
        $cron->run();
        $cron->run();
        $assertIndexerStatus->processAssert($indexManagement, $indexers, true);
        $assertCatalogPriceRuleAppliedProductPage
            ->processAssert($catalogProductViewPage, $cmsIndexPage, $catalogCategoryViewPage, $products, $productPrice2, $customer);
        $this->catalogRuleIndex->open();
        $this->catalogRuleIndex->getCatalogRuleGrid()->searchAndOpen($filter);
        $this->catalogRuleNew->getFormPageActions()->delete();
        $this->catalogRuleNew->getModalBlock()->acceptAlert();
        $assertIndexerStatus->processAssert($indexManagement, $indexers, false);
        $assertCatalogPriceRuleAppliedProductPage
            ->processAssert($catalogProductViewPage, $cmsIndexPage, $catalogCategoryViewPage, $products, $productPrice2, $customer);
        $cron->run();
        $cron->run();
        $assertIndexerStatus->processAssert($indexManagement, $indexers, true);
        $assertCatalogPriceRuleNotAppliedProductPage
            ->processAssert($catalogProductViewPage, $cmsIndexPage, $catalogCategoryViewPage, $products);

        return ['products' => $products];
    }
}
