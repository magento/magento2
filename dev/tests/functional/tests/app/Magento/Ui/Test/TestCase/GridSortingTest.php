<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\TestCase;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Page\PageFactory;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Precondition:
 * 1. Create items
 *
 * Steps:
 * 1. Navigate to backend.
 * 2. Go to grid page
 * 3. Sort grid using provided columns
 * 5. Perform Asserts
 *
 * @group Ui_(CS)
 * @ZephyrId MAGETWO-41328
 */
class GridSortingTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'CS';
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
     * @param string $fixtureName
     * @param string $fixtureDataSet
     * @param int $itemsCount
     * @param array $steps
     * @param string $pageClass
     * @param string $gridRetriever
     * @param array $columnsForSorting
     * @return array
     */
    public function test(
        $pageClass,
        $gridRetriever,
        array $columnsForSorting,
        $fixtureName = null,
        $fixtureDataSet = null,
        $itemsCount = null,
        array $steps = []
    ) {
        // Fill grid before sorting if needed
        if ($fixtureName && $fixtureDataSet && $itemsCount && $steps) {
            $this->createItems($itemsCount, $fixtureName, $fixtureDataSet, $steps);
        }

        $page = $this->pageFactory->create($pageClass);

        // Steps
        $page->open();
        /** @var DataGrid $gridBlock */
        $gridBlock = $page->$gridRetriever();
        $gridBlock->resetFilter();

        $sortingResults = [];
        foreach ($columnsForSorting as $columnName) {
            $gridBlock->sortByColumn($columnName);
            $sortingResults[$columnName]['firstIdAfterFirstSoring'] = $gridBlock->getFirstItemId();
            $gridBlock->sortByColumn($columnName);
            $sortingResults[$columnName]['firstIdAfterSecondSoring'] = $gridBlock->getFirstItemId();
        }

        return ['sortingResults' => $sortingResults];
    }

    /**
     * @param int $itemsCount
     * @param string $fixtureName
     * @param string $fixtureDataSet
     * @param string $steps
     * @return array
     */
    protected function createItems($itemsCount, $fixtureName, $fixtureDataSet, $steps)
    {
        $items = [];
        for ($i = 0; $i < $itemsCount; $i++) {
            $item = $this->fixtureFactory->createByCode($fixtureName, ['dataset' => $fixtureDataSet]);
            $item->persist();
            $items[$i] = $item;
            $this->processSteps($item, $steps[$i]);
        }

        return $items;
    }

    /**
     * @param FixtureInterface $item
     * @param string $steps
     */
    protected function processSteps(FixtureInterface $item, $steps)
    {
        if (!is_array($steps) && $steps != '-') {
            $steps = [$steps];
        } elseif ($steps == '-') {
            $steps = [];
        }
        foreach ($steps as $step) {
            $processStep = $this->objectManager->create($step, ['order' => $item]);
            $processStep->run();
        }
    }
}
