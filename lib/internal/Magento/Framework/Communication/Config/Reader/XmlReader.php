<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config\Reader;

/**
 * Communication configuration filesystem reader. Reads data from XML configs.
 */
class XmlReader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = [
        '/config/topic' => 'name',
        '/config/topic/handler' => 'name'
    ];
}
