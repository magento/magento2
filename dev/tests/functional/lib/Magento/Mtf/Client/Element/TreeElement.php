<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Element;

/**
 * Typified element class for Tree elements.
 */
class TreeElement extends Tree
{
    /**
     * Css class for finding tree nodes.
     *
     * @var string
     */
    protected $nodeCssClass = '.x-tree-node > .x-tree-node-ct';

    /**
     * Css class for detecting tree nodes.
     *
     * @var string
     */
    protected $nodeSelector = '.x-tree-node';

    /**
     * Css class for fetching node's name.
     *
     * @var string
     */
    protected $nodeName = 'div > a';
}
