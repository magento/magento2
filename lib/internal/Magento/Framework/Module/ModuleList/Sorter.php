<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module\ModuleList;

/**
 * Topological module dependency sorter.
 */
class Sorter
{
    /**
     * @var array
     */
    private $origList = [];

    /**
     * @var array
     */
    private $elements = [];

    /**
     * @var array
     */
    private $sortedModules = [];

    /**
     * @param array $origList
     * @return array
     */
    public function sort(array $origList)
    {
        $this->origList = $origList;

        foreach ($origList as $moduleName => $moduleInformation) {
            $this->elements[$moduleName] = $moduleInformation;
            $this->elements[$moduleName]['isProcessed'] = false;
        }

        $this->sortedModules = [];

        foreach ($this->elements as $element) {
            $this->processElement($element, []);
        }

        return $this->sortedModules;
    }

    /**
     * @param array $element
     * @param array $parentElements
     * @throws \Exception
     */
    private function processElement($element, $parentElements = [])
    {
        if (isset($parentElements[$element['name']])) {
            $relatedName = implode('', array_slice(array_keys($parentElements), -1, 1));

            throw new \Exception(
                "Circular sequence reference from '{$relatedName}' to '{$element['name']}'."
            );
        }

        if (!$element['isProcessed']) {
            $parentElements[$element['name']] = true;

            $element['isProcessed'] = true;

            foreach ($element['sequence'] as $sequence) {
                if (isset($this->elements[$sequence])) {
                    $this->processElement($this->elements[$sequence], $parentElements);
                }
            }

            unset($element['isProcessed']);
            $this->sortedModules[$element['name']] = $element;
        }
    }
}
