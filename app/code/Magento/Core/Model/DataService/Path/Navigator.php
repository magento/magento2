<?php
/**
 * Navigates the DataService path.
 *
 * DataServices can be represented by a path, for example {root.branch.leaf} could be a way to point to
 * a specific 'leaf' data service that lives within the context of 'branch' which itself is found under
 * the 'root' data service. What we are trying to solve here is an efficient and easy to use method of
 * accessing a specific data service within an existing hierarchy.
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\DataService\Path;

class Navigator
{
    /**
     * Searches a root node using a given path for a specific child node.
     *
     * @param \Magento\Core\Model\DataService\Path\NodeInterface|array $root
     *        Root node in the graph from which to start the search.
     * @param array $path path to use for searching.
     * @throws \InvalidArgumentException if $root is null or if path can't be followed to a leaf node.
     * @return mixed
     */
    public function search($root, array $path)
    {
        if (null === $root) {
            throw new \InvalidArgumentException('Search contained null root.');
        }
        $pathElement = array_shift($path);

        $childElement = null;
        if (is_array($root)) {
            if (array_key_exists($pathElement, $root)) {
                $childElement = $root[$pathElement];
            }
        } else {
            $childElement = $root->getChildNode($pathElement);
        }

        if (empty($path)) {
            return $childElement;
        }
        try {
            return $this->search($childElement, $path);
        } catch (\InvalidArgumentException $iae) {
            throw new \InvalidArgumentException(
                'Search failed to find an intermediate node with given path: '
                . $pathElement . '.' . join('.', $path)
            );
        }
    }
}
