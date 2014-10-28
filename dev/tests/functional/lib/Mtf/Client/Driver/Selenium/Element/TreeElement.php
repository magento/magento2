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

namespace Mtf\Client\Driver\Selenium\Element;

/**
 * Class TreeElement
 * Typified element class for Tree elements
 *
 */
class TreeElement extends Tree
{
    /**
     * Css class for finding tree nodes
     *
     * @var string
     */
    protected $nodeCssClass = '.x-tree-node > .x-tree-node-ct';

    /**
     * Css class for detecting tree nodes
     *
     * @var string
     */
    protected $nodeSelector = '.x-tree-node';

    /**
     * Css class for fetching node's name
     *
     * @var string
     */
    protected $nodeName = 'div > a';

    /**
     * Get structure of the tree element
     *
     * @return array
     */
    public function getStructure()
    {
        return $this->_getNodeContent($this, '.x-tree-root-node');
    }
}
