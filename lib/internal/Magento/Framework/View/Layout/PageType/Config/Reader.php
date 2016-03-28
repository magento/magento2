<?php
/**
 * Page layout config reader
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\PageType\Config;

class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of identifier attributes for merging
     *
     * @var array
     */
    protected $_idAttributes = ['/page_types/type' => 'id'];
}
