<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Layout\Config;

use Magento\Framework\Config\Reader\Filesystem;

/**
 * Page layout config reader
 */
class Reader extends Filesystem
{
    /**
     * List of identifier attributes for merging
     *
     * @var array
     */
    protected $_idAttributes = ['/page_layouts/layout' => 'id'];
}
