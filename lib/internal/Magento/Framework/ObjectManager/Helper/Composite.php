<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Helper;

/**
 * Helper for classes which implement Composite pattern.
 */
class Composite
{
    /**
     * @param array $declaredComponents Array of the components which should be registered in the following format:
     * <pre>
     * [
     *      ['type' => $firstComponentObject, 'sortOrder' => 15],
     *      ['type' => $secondComponentObject, 'sortOrder' => 10],
     *      ...
     * ]
     * </pre>
     * @return array Array of components declarations. Items are sorted and misconfigured ones are removed.
     */
    public function filterAndSortDeclaredComponents($declaredComponents)
    {
        /** Eliminate misconfigured components */
        $declaredComponents = array_filter(
            $declaredComponents,
            function ($component) {
                return (isset($component['type']) && isset($component['sortOrder']));
            }
        );
        /** Sort all components according to the provided sort order */
        uasort(
            $declaredComponents,
            function ($firstComponent, $secondComponent) {
                $firstComponentSortOrder = (int)$firstComponent['sortOrder'];
                $secondComponentSortOrder = (int)$secondComponent['sortOrder'];
                if ($firstComponentSortOrder == $secondComponentSortOrder) {
                    return 0;
                } elseif ($firstComponentSortOrder < $secondComponentSortOrder) {
                    return -1;
                } else {
                    return 1;
                }
            }
        );
        $declaredComponents = array_values($declaredComponents);
        return $declaredComponents;
    }
}
