<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Product\Compare;

/**
 * Block for displaying link on top menu
 */
class Link extends \Magento\Framework\View\Element\Template
{
    /**
     * The property is used to define content-scope of block. Can be private or public.
     * If it isn't defined then application considers it as false.
     *
     * @var bool
     */
    protected $_isScopePrivate = true;
}
