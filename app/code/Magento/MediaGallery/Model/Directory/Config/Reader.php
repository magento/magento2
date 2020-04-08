<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory\Config;

use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Config\Reader\Filesystem;

/**
 * Media gallery directory config reader
 */
class Reader extends Filesystem implements ReaderInterface
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = [
        '/config/patterns' => 'patterns',
        '/config/patterns/pattern' => 'name',
    ];
}
