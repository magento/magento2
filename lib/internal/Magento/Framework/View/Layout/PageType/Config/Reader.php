<?php
/**
 * Page layout config reader
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\PageType\Config;

/**
 * Class \Magento\Framework\View\Layout\PageType\Config\Reader
 *
 * @since 2.0.0
 */
class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of identifier attributes for merging
     *
     * @var array
     * @since 2.0.0
     */
    protected $_idAttributes = ['/page_types/type' => 'id'];
}
