<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Webapi\Model\Config;

/**
 * Service config data reader.
 */
class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = [
        '/routes/route' => ['url', 'method'],
        '/routes/route/resources/resource' => 'ref',
        '/routes/route/data/parameter' => 'name',
    ];
}
