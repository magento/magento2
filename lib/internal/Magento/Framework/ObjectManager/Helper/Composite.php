<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
                } else if ($firstComponentSortOrder < $secondComponentSortOrder) {
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
