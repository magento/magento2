<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Category;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\Util\Command\Cli\Cron;
use Magento\Mtf\Util\Command\Cli\Indexer;
use Magento\Store\Test\Fixture\Store;

/**
 * Test Flow:
 * Preconditions:
 * 1. Create category.
 * 2. Create two stores.
 * 3. Set configuration settings.
 * 4. Run cron twice.
 * 5. Perform full reindex: "bin/magento indexer:reindex".
 *
 * Steps:
 * 1. Login as admin.
 * 2. Navigate Products->Categories.
 * 3. Open category created in preconditions.
 * 4. Update data according to data set.
 * 5. Save.
 * 6. Perform asserts.
 *
 * @group Category_Management
 * @ZephyrId MAGETWO-20169
 */
class UpdateCategoryEntityFlatDataTest extends UpdateCategoryEntityTest
{
    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Perform bin/magento commands for reindex indexers.
     *
     * @var Indexer
     */
    private $indexer;

    /**
     * Handle cron for tests executions.
     *
     * @var Cron
     */
    private $cron;

    /**
     * Configuration data.
     *
     * @var string
     */
    private $configData;

    /**
     * Prepare test data.
     *
     * @param Cron $cron
     * @param Indexer $indexer
     * @param TestStepFactory $stepFactory
     * @return void
     */
    public function __prepare(Cron $cron, Indexer $indexer, TestStepFactory $stepFactory)
    {
        $this->cron = $cron;
        $this->indexer = $indexer;
        $this->stepFactory = $stepFactory;
    }

    /**
     * Test for update category if use category flat.
     *
     * @param Category $category
     * @param Category $initialCategory
     * @param Store|null $firstStore
     * @param Store|null $secondStore
     * @param array|null $indexersMode
     * @param string|null $configData
     * @return array
     */
    public function test(
        Category $category,
        Category $initialCategory,
        Store $firstStore = null,
        Store $secondStore = null,
        $indexersMode = null,
        $configData = null
    ) {
        $this->configData = $configData;

        //Preconditions
        $firstStore->persist();
        $secondStore->persist();
        $this->cron->run();
        $this->cron->run();

        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'flushCache' => true]
        )->run();

        $this->indexer->setMode($indexersMode);
        $this->indexer->reindex();

        return parent::test($category, $initialCategory);
    }

    /**
     * Set default configuration.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true, 'flushCache' => true]
        )->run();
        $this->indexer->reindex();
    }
}
