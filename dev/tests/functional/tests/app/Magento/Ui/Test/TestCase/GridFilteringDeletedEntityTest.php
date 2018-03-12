<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Page\PageFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Precondition:
 * 1. Create items
 *
 * Steps:
 * 1. Log in to Admin.
 * 2. Go to grid page.
 * 3. Apply filter by Store View.
 * 4. Delete Website.
 * 5. Go to grid page.
 * 6. Perform Asserts.
 *
 * @group Ui
 * @ZephyrId MAGETWO-89042
 */
class GridFilteringDeletedEntityTest extends Injectable
{
    /* tags */
    const SEVERITY = 'S2';
    const MVP = 'no';
    /* end tags */

    /**
     * Order index page.
     *
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Injection data.
     *
     * @param PageFactory $pageFactory
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(PageFactory $pageFactory, FixtureFactory $fixtureFactory)
    {
        $this->pageFactory = $pageFactory;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * @param string $pageClass
     * @param string $gridRetriever
     * @param string[] $filters
     * @param string $fixtureName
     * @param string[] $steps
     * @param array $fixtureDataSet
     * @return void
     */
    public function test(
        $pageClass,
        $gridRetriever,
        array $filters,
        $fixtureName,
        array $steps = [],
        $fixtureDataSet = null
    ) {
        $item = $this->createItems($fixtureName, $fixtureDataSet);
        $page = $this->pageFactory->create($pageClass);

        $page->open();
        /** @var DataGrid $gridBlock */
        $gridBlock = $page->$gridRetriever();
        $gridBlock->resetFilter();

        foreach ($filters as $itemFilters) {
            $filterArray = [];
            foreach ($itemFilters as $itemFiltersName => $itemFilterValue) {
                if (substr($itemFilterValue, 0, 1) === ':') {
                    $value = $item->getData(substr($itemFilterValue, 1));
                } else {
                    $value = $itemFilterValue;
                }
                $filterArray[$itemFiltersName] = $value;
            }

            $storesArray = $item->getDataFieldConfig('website_ids')['source']->getStores();
            $store = end($storesArray);
            $filterArray['store_id']  = $store->getName();
            $gridBlock->search($filterArray);
        }

        if (!empty($steps)) {
            foreach ($steps as $step) {
                $this->processSteps($item, $step);
            }
        }
    }

    /**
     * @param string $fixtureName
     * @param string $fixtureDataSet
     * @return FixtureInterface
     */
    private function createItems($fixtureName, $fixtureDataSet)
    {
        $item = $this->fixtureFactory->createByCode($fixtureName, ['dataset' => $fixtureDataSet]);
        $item->persist();
        return $item;
    }

    /**
     * @param FixtureInterface $item
     * @param array $steps
     * @return void
     */
    private function processSteps(FixtureInterface $item, $steps)
    {
        foreach ($steps as $step) {
            $processStep = $this->objectManager->create($step, ['item' => $item]);
            $processStep->run();
        }
    }
}
