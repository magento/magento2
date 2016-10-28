<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\TestCase;

use Magento\Indexer\Test\Constraint\AssertUpdateByScheduleSuccessSaveMessage as AssertSuccessSaveMessage;
use Magento\CatalogSearch\Test\Page\AdvancedSearch;
use Magento\Indexer\Test\Page\Adminhtml\IndexManagement;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Util\Command\Cli\Indexer;
use Magento\CatalogSearch\Test\Constraint\AssertSearchAttributeTest;
use Magento\Indexer\Test\Constraint\AssertIndexerStatus;
use Magento\Catalog\Test\Constraint\AssertProductAttributeSaveMessage;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeNew;
use Magento\CatalogSearch\Test\Page\AdvancedResult;
use Magento\CatalogSearch\Test\Constraint\AssertAdvancedSearchProductResult;
use Magento\Catalog\Test\Constraint\AssertProductSaveMessage;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Fixture\Category;

/**
 * Preconditions:
 * 1. Backend -> System -> New Index Management
 * 2. Product EAV = Update by Schedule
 *      Cron is turned off.
 * 3. Perform full reindex: "bin/magento indexer:reindex".
 * Steps:
 * 1. Call assert to check index status (Product EAV indexer: Status = Ready)
 * 2. Open Backend -> Stores -> Attributes -> Product
 * 3. Open Weight attribute
 * 4. Update and save attribute to:
 *      Use in Advanced Search = Yes
 * 5. Call assert to check index status (Product EAV indexer: Status = Required Reindex)
 * 6. Assert that weight attribute is available on the Advanced Search
 * 7. Run Full reindex from console
 * 8. Change Weight attribute and save
 *      Scope = Website (Advanced Attribute Properties)
 * 10. Call assert to check index status (Product EAV indexer: Status = Required Reindex)
 * 11. Assert that weight attribute is available on the Advanced Search
 * 12. Run Full reindex from console
 * 13. Create simple product with default attribute set with weight = 1
 * 14. Create grouped product so that it will include simple product as option
 * 15. Create bundle product so that it will include simple product as option
 * 16. Create configurable product with one option product for which weight = 2
 * 17. Call assert to check index status (Product EAV indexer: Status = Ready
 * 18. Open Advanced Search on frontend
 * 19. Enter value to Weight = 1 and click Search button
 * 20. Assert that page with 3 products is open:
 *      Simple
 *      Bundle
 *      Grouped
 * 21. Update Weight Attribute in Backend
 *      Use in Advanced Search = No
 * 22. Call assert to check index status (Product EAV indexer: Status = Required Reindex)
 * 23. Assert that weight attribute is absent the Advanced Search
 * 24. Run Full reindex from console
 *
 * @group Search
 * @ZephyrId MAGETWO-25931
 */
class AdvancedSearchWithAttributeTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Assert that Attribute is present in Advanced Search Page.
     *
     * @var AssertSearchAttributeTest
     */
    private $assertSearchAttributeTest;

    /**
     * Products for search
     *
     * @var array
     */
    private $products;

    /**
     * Attribute for check in Advanced Search Page.
     *
     * @var string
     */
    private $attributeForSearch;

    /**
     * Default weight attribute value.
     *
     * @var CatalogProductAttribute
     */
    private $attributeDisable;

    /**
     * Indexers in Index Management Page.
     *
     * @var array
     */
    private $indexers;

    /**
     * Advanced Search Page.
     *
     * @var AdvancedSearch
     */
    private $advancedSearch;

    /**
     * Index Management Page.
     *
     * @var IndexManagement
     */
    private $indexManagement;

    /**
     * Perform bin/magento commands from command line for functional tests executions.
     *
     * @var Indexer
     */
    private $cli;

    /**
     * Catalog Product Attribute Index Page.
     *
     * @var AttributePage
     */
    private $attributePage;

    /**
     * Advanced Result Page.
     *
     * @var ResultPage
     */
    private $resultPage;

    /**
     * Catalog Product Index Page.
     *
     * @var ProductGrid
     */
    private $productGrid;

    /**
     * Catalog Product New Page.
     *
     * @var NewProductPage
     */
    private $newProductPage;

    /**
     * Catalog Product Edit Page.
     *
     * @var ProductEdit
     */
    private $productEdit;

    /**
     * Catalog Product Attribute New Page.
     *
     * @var AttributeNewPage
     */
    private $attributeNewPage;

    /**
     * Assert Indexer Status.
     *
     * @var AssertIndexerStatus
     */
    private $assertIndexerStatus;

    /**
     * Assert Creation Product.
     *
     * @var AssertProductSaveMessage
     */
    private $assertCreateProducts;

    /**
     * Assert Creation Product.
     *
     * @var AssertCreateProducts
     */
    private $productAttributePage;

    /**
     * Assert Success Message Indexer Update by Schedule.
     *
     * @var AssertSuccessSaveMessage
     */
    private $assertSuccessSaveMessage;

    /**
     * Assert Success Message is Present After Save Attribute.
     *
     * @var AssertAdvancedSearchResult
     */
    private $assertAdvancedSearchResult;

    /**
     * Assert Products in Advanced Search Result Page.
     *
     * @var AssertAttributeStatus
     */
    private $assertAttributeStatus;

    /**
     * Inject pages.
     *
     * @param IndexManagement $indexManagement
     * @param AdvancedSearch $advancedSearch
     * @param AdvancedResult $resultPage
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductNew $newProductPage
     * @param CatalogProductEdit $productEdit
     * @param AssertIndexerStatus $assertIndexerStatus
     * @param AssertProductSaveMessage $assertCreateProducts
     * @param CatalogProductAttributeIndex $productAttributePage
     * @param CatalogProductAttributeNew $attributeNewPage
     * @param AssertSuccessSaveMessage $assertSuccessSaveMessage
     * @param AssertSearchAttributeTest $assertSearchAttributeTest
     * @param AssertAdvancedSearchProductResult $assertAdvancedSearchResult
     * @param AssertProductAttributeSaveMessage $assertAttributeStatus
     * @return void
     */
    public function __inject(
        IndexManagement $indexManagement,
        AdvancedSearch $advancedSearch,
        AdvancedResult $resultPage,
        CatalogProductIndex $productGrid,
        CatalogProductNew $newProductPage,
        CatalogProductEdit $productEdit,
        AssertIndexerStatus $assertIndexerStatus,
        AssertProductSaveMessage $assertCreateProducts,
        CatalogProductAttributeIndex $productAttributePage,
        CatalogProductAttributeNew $attributeNewPage,
        AssertSuccessSaveMessage $assertSuccessSaveMessage,
        AssertSearchAttributeTest $assertSearchAttributeTest,
        AssertAdvancedSearchProductResult $assertAdvancedSearchResult,
        AssertProductAttributeSaveMessage $assertAttributeStatus
    ) {
        $this->indexManagement = $indexManagement;
        $this->advancedSearch = $advancedSearch;
        $this->productAttributePage = $productAttributePage;
        $this->resultPage = $resultPage;
        $this->productGrid = $productGrid;
        $this->newProductPage = $newProductPage;
        $this->productEdit = $productEdit;
        $this->assertIndexerStatus = $assertIndexerStatus;
        $this->assertCreateProducts = $assertCreateProducts;
        $this->attributeNewPage = $attributeNewPage;
        $this->assertSuccessSaveMessage = $assertSuccessSaveMessage;
        $this->assertSearchAttributeTest = $assertSearchAttributeTest;
        $this->assertAdvancedSearchResult = $assertAdvancedSearchResult;
        $this->assertAttributeStatus = $assertAttributeStatus;
    }

    /**
     * Use Advanced Search by Decimal indexable attribute if Edit/Add Attribute.
     *
     * @param Indexer $cli
     * @param Category $category
     * @param FixtureFactory $fixtureFactory
     * @param CatalogProductSimple $productSearch
     * @param CatalogProductAttribute $attributeEnable
     * @param CatalogProductAttribute $attributeDisable
     * @param CatalogProductAttribute $attributeGlobalStatus
     * @param string $attributeForSearch
     * @param array $isVisibleInAdvancedSearch
     * @param array $productDropDownList
     * @param array $products
     * @param string|null $indexers
     * @return void
     */
    public function test(
        Indexer $cli,
        Category $category,
        FixtureFactory $fixtureFactory,
        CatalogProductSimple $productSearch,
        CatalogProductAttribute $attributeEnable,
        CatalogProductAttribute $attributeDisable,
        CatalogProductAttribute $attributeGlobalStatus,
        $attributeForSearch,
        array $isVisibleInAdvancedSearch,
        array $productDropDownList,
        array $products,
        $indexers = null
    ) {
        $this->cli = $cli;
        $this->products = $products;
        $this->attributeDisable = $attributeDisable;
        $this->attributeForSearch = $attributeForSearch;
        $this->indexers = explode(',', $indexers);

        $category->persist();

        // Indexers Update bu Schedule
        $this->indexManagement->open();
        $this->indexManagement->getMainBlock()->updateBySchedule($this->indexers);
        //Assert attribute Update by Schedule
        $this->assertSuccessSaveMessage->processAssert($this->indexManagement, $this->indexers);

        // Full indexers reindex
        $cli->reindex();
        // Assert indexers status
        $this->assertIndexerStatus->processAssert($this->indexManagement, $this->indexers);
        $this->productAttributePage->open();
        $this->productAttributePage->getGrid()->searchAndOpen(['attribute_code' => $attributeForSearch]);
        $this->attributeNewPage->getAttributeForm()->fill($attributeEnable);
        $this->attributeNewPage->getPageActions()->save();
        // Assert attribute status
        $this->assertAttributeStatus->processAssert($this->productAttributePage);

        // Assert indexers status
        $this->assertIndexerStatus->processAssert($this->indexManagement, $this->indexers, false);

        $this->assertSearchAttributeTest->processAssert($this->advancedSearch, $attributeForSearch);
        $cli->reindex();

        // Change attribute 'scope mode'
        $this->productAttributePage->open();
        $this->productAttributePage->getGrid()->searchAndOpen(['attribute_code' => $attributeForSearch]);
        $this->attributeNewPage->getAttributeForm()->fill($attributeGlobalStatus);
        $this->attributeNewPage->getPageActions()->save();
        // Assert attribute status
        $this->assertAttributeStatus->processAssert($this->productAttributePage);

        // Assert indexers status
        $this->assertIndexerStatus->processAssert($this->indexManagement, $this->indexers, false);

        // Assert advanced attribute is present(or absent) in Advanced Search Page.
        $this->assertSearchAttributeTest->processAssert($this->advancedSearch, $attributeForSearch);
        $cli->reindex();

        // Create Products
        $allProducts = [];
        foreach ($products as $key => $product) {
            list($fixtureCode, $dataset) = explode('::', $product);
            $this->productGrid->open();
            $this->productGrid->getGridPageActionBlock()->addProduct($productDropDownList[$key]);
            $product = $fixtureFactory->createByCode($fixtureCode, ['dataset' => $dataset]);
            $this->newProductPage->getProductForm()->fill($product, null, $category);
            $this->newProductPage->getFormPageActions()->save($product);

            $this->assertCreateProducts->processAssert($this->productEdit);
            $allProducts[] = $product;
        }

        $cli->reindex();
        $this->advancedSearch->open();
        $this->advancedSearch->getForm()->fill($productSearch)->submit();

        // Assert that Advanced Search result page contains only product(s) according to requested from fixture
        $this->assertAdvancedSearchResult->processAssert($isVisibleInAdvancedSearch, $allProducts, $this->resultPage);
        $this->productAttributePage->open();
        $this->productAttributePage->getGrid()->searchAndOpen(['attribute_code' => $this->attributeForSearch]);
        $this->attributeNewPage->getAttributeForm()->fill($this->attributeDisable);
        $this->attributeNewPage->getPageActions()->save();
        // Assert attribute status
        $this->assertAttributeStatus->processAssert($this->productAttributePage);

        $this->assertIndexerStatus->processAssert($this->indexManagement, $this->indexers, false);
        $cli->reindex();
        $this->assertSearchAttributeTest->processAssert(
            $this->advancedSearch,
            $this->attributeForSearch,
            false
        );
    }

    /**
     * Set attribute default value.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->productAttributePage->open();
        $this->productAttributePage->getGrid()->searchAndOpen(['attribute_code' => $this->attributeForSearch]);
        $this->attributeNewPage->getAttributeForm()->fill($this->attributeDisable);
        $this->attributeNewPage->getPageActions()->save();
        $this->cli->reindex();
    }
}
