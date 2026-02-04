<?php
/**
 * Page layout config reader
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
