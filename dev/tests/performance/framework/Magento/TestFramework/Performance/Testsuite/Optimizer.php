<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Optimizer for scenario queue.
 * Sorts sets of fixtures in order to minimize number of Magento reinstalls between their scenario executions.
 */
namespace Magento\TestFramework\Performance\Testsuite;

class Optimizer
{
    /**
     * Sort sets of fixtures in such an order, that number of Magento reinstalls between executions of their scenarios
     * would be reduced.
     *
     * @param array $sets Array of fixture sets
     * @return array Keys of $sets, sorted in an optimized order
     */
    public function optimizeFixtureSets(array $sets)
    {
        $sorted = [];
        $currentSet = null;
        while ($sets) {
            $chosenKey = null;
            if ($currentSet) {
                $chosenKey = $this->_chooseSmallestSuperSet($currentSet, $sets);
            }
            if (!$chosenKey) {
                $chosenKey = $this->_chooseSmallestSet($sets);
            }
            $sorted[] = $chosenKey;

            $currentSet = $sets[$chosenKey];
            unset($sets[$chosenKey]);
        }

        return $sorted;
    }

    /**
     * Search through $pileOfSets to find a set, that contains same items as in $set plus some additional items.
     * Prefer the set with the smallest number of items.
     *
     * @param array $set
     * @param array $pileOfSets
     * @return mixed Key or null, if key not found
     */
    protected function _chooseSmallestSuperSet(array $set, array $pileOfSets)
    {
        $chosenKey = null;
        $chosenNumItems = null;
        foreach ($pileOfSets as $key => $checkSet) {
            if (array_diff($set, $checkSet)) {
                // $checkSet is not a super set, as it doesn't have some items of $set
                continue;
            }

            $numItems = count($checkSet);
            if ($chosenKey === null || $chosenNumItems > $numItems) {
                $chosenKey = $key;
                $chosenNumItems = $numItems;
            }
        }

        return $chosenKey;
    }

    /**
     * Find a set that has the smallest number of items
     *
     * @param array $sets
     * @return mixed Key of a selected set
     */
    protected function _chooseSmallestSet(array $sets)
    {
        $chosenKey = key($sets);
        foreach ($sets as $key => $set) {
            if (count($sets[$chosenKey]) > count($set)) {
                $chosenKey = $key;
            }
        }

        return $chosenKey;
    }
}
