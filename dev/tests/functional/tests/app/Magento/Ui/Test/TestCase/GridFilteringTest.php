<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
 * 3. Filter grid using provided columns
 * 5. Perform Asserts
 *
 * @group Ui
 * @ZephyrId MAGETWO-41329
 */
class GridFilteringTest extends Injectable
{
    /* tags */
    const SEVERITY = 'S2';
    const STABLE = 'no';
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
     * @param string $fixtureName
     * @param string $fixtureDataSet
     * @param int $itemsCount
     * @param array $steps
     * @param string $pageClass
     * @param string $gridRetriever
     * @param string $idGetter
     * @param array $filters
     * @param string $idColumn
     * @return array
     */
    public function test(
        $pageClass,
        $gridRetriever,
        $idGetter,
        array $filters,
        $fixtureName,
        $itemsCount,
        array $steps = [],
        $fixtureDataSet = null,
        $idColumn = null
    ) {
        $items = $this->createItems($itemsCount, $fixtureName, $fixtureDataSet, $steps);
        $page = $this->pageFactory->create($pageClass);

        // Steps
        $page->open();
        /** @var DataGrid $gridBlock */
        $gridBlock = $page->$gridRetriever();
        $gridBlock->resetFilter();

        $filterResults = [];
        foreach ($filters as $index => $itemFilters) {
            foreach ($itemFilters as $itemFiltersName => $itemFilterValue) {
                if (substr($itemFilterValue, 0, 1) === ':') {
                    $value = $items[$index]->getData(substr($itemFilterValue, 1));
                } else {
                    $value = $itemFilterValue;
                }
                $gridBlock->search([$itemFiltersName => $value]);
                $idsInGrid = $gridBlock->getAllIds();
                if ($idColumn) {
                    $filteredTargetIds = [];
                    foreach ($idsInGrid as $filteredId) {
                        $filteredTargetIds[] = $gridBlock->getColumnValue($filteredId, $idColumn);
                    }
                    $idsInGrid = $filteredTargetIds;
                }
                $filteredIds = $this->getActualIds($idsInGrid, $items, $idGetter);
                $filterResults[$items[$index]->$idGetter()][$itemFiltersName] = $filteredIds;
            }
        }

        return ['filterResults' => $filterResults];
    }

    /**
     * @param string[] $ids
     * @param FixtureInterface[] $items
     * @param string $idGetter
     * @return string[]
     */
    protected function getActualIds(array $ids, array $items, $idGetter)
    {
        $actualIds = [];
        foreach ($items as $item) {
            if (in_array($item->$idGetter(), $ids)) {
                $actualIds[] = $item->$idGetter();
            }
        }
        return  $actualIds;
    }

    /**
     * @param int $itemsCount
     * @param string $fixtureName
     * @param string $fixtureDataSet
     * @param string $steps
     * @return FixtureInterface[]
     */
    protected function createItems($itemsCount, $fixtureName, $fixtureDataSet, $steps)
    {
        $items = [];
        for ($i = 0; $i < $itemsCount; $i++) {
            $item = $this->fixtureFactory->createByCode($fixtureName, ['dataset' => $fixtureDataSet]);
            $item->persist();
            $items[$i] = $item;
            if (!empty($steps)) {
                $this->processSteps($item, $steps[$i]);
            }
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
            $products = $item->getEntityId()['products'];
            $cart['data']['items'] = ['products' => $products];
            $cart = $this->fixtureFactory->createByCode('cart', $cart);
            $processStep = $this->objectManager->create($step, ['order' => $item, 'cart' => $cart]);
            $processStep->run();
        }
    }
}
