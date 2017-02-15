<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Category;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Util\Command\Cli\Indexer;
use Magento\Mtf\Util\Command\Cli\Cron;

/**
 * Test Creation for UpdateCategoryEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Create category
 *
 * Steps:
 * 1. Login as admin
 * 2. Navigate Products->Categories
 * 3. Open category created in preconditions
 * 4. Update data according to data set
 * 5. Save
 * 6. Perform asserts
 *
 * @group Category_Management
 * @ZephyrId MAGETWO-20169
 */
class UpdateCategoryEntityFlatDataTest extends UpdateCategoryEntityTest
{
    /**
     * Perform bin/magento commands for reindex indexers.
     *
     * @var Indexer
     */
    private $indexer;

    /**
     * Should cache be flushed.
     *
     * @var bool
     */
    private $flushCache;

    /**
     * Configuration Data
     *
     * @var ValueInterface $configData
     */
    private $configData;

    /**
     * Inject page end prepare default category
     *
     * @param Category $initialCategory
     * @param CatalogCategoryIndex $catalogCategoryIndex
     * @param CatalogCategoryEdit $catalogCategoryEdit
     * @param FixtureFactory $fixtureFactory
     * @param Indexer $indexer
     * @return array
     */
    public function __inject(
        Category $initialCategory,
        CatalogCategoryIndex $catalogCategoryIndex,
        CatalogCategoryEdit $catalogCategoryEdit,
        FixtureFactory $fixtureFactory,
        Indexer $indexer
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->catalogCategoryIndex = $catalogCategoryIndex;
        $this->catalogCategoryEdit = $catalogCategoryEdit;
        $this->indexer = $indexer;
        $initialCategory->persist();
        return ['initialCategory' => $initialCategory];
    }

    /**
     * Test for update category
     *
     * @param Category $category
     * @param Category $initialCategory
     * @param null $indexersMode
     * @param $configData
     * @param bool $flushCache
     * @param Cron $cron
     * @return array
     */
    public function test(
        Category $category,
        Category $initialCategory,
        Cron $cron,
        $indexersMode = null,
        $configData = null,
        $flushCache = true
    ) {
        $this->flushCache = $flushCache;
        $this->configData = $configData;

        //Preconditions
        $firstStore = $this->fixtureFactory->createByCode('store', ['dataset' => 'custom']);
        $secondStore = $this->fixtureFactory->createByCode('store', ['dataset' => 'custom']);
        $firstStore->persist();
        $secondStore->persist();
        $cron->run();
        $cron->run();



        if (isset($this->configData)) {
            $this->objectManager->create(
                \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
                ['configData' => $this->configData, 'flushCache' => $this->flushCache]
            )->run();
        }

        if (isset($indexersMode)) {
            $this->indexer->setMode($indexersMode);
        }
        $this->indexer->reindex();

        $this->catalogCategoryIndex->open();
        $this->catalogCategoryIndex->getTreeCategories()->selectCategory($initialCategory);
        $this->catalogCategoryEdit->getEditForm()->fill($category);
        $this->catalogCategoryEdit->getFormPageActions()->save();
        return ['category' => $this->prepareCategory($category, $initialCategory)];
    }


    /**
     * Set default configuration.
     *
     * @return void
     */
    public function tearDown()
    {
        if (isset($this->configData)) {
            $this->objectManager->create(
                \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
                ['configData' => $this->configData, 'rollback' => true, 'flushCache' => $this->flushCache]
            )->run();
        }
        $this->indexer->reindex();
    }
}
