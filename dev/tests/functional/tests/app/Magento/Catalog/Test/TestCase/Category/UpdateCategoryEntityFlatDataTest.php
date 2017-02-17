<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Category;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\Util\Command\Cli\Indexer;
use Magento\Mtf\Util\Command\Cli\Cron;

/**
 * Test Creation for UpdateCategoryEntityFlatData
 *
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
     * @return array
     */
    public function __prepare(Cron $cron, Indexer $indexer)
    {
        $this->cron = $cron;
        $this->indexer = $indexer;
    }

    /**
     * Test for update category if use category flat.
     *
     * @param Category $category
     * @param Category $initialCategory
     * @param null $indexersMode
     * @param string|null $configData
     * @return array
     */
    public function test(
        Category $category,
        Category $initialCategory,
        $indexersMode = null,
        $configData = null
    ) {
        $this->configData = $configData;

        //Preconditions
        $firstStore = $this->fixtureFactory->createByCode('store', ['dataset' => 'custom']);
        $secondStore = $this->fixtureFactory->createByCode('store', ['dataset' => 'custom']);
        $firstStore->persist();
        $secondStore->persist();
        $this->cron->run();
        $this->cron->run();

        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'flushCache' => true]
        )->run();

        if ($indexersMode !== null) {
            $this->indexer->setMode($indexersMode);
        }
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
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true, 'flushCache' => true]
        )->run();
        $this->indexer->reindex();
    }
}
