<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\HTTP\Adapter;

use Magento\Framework\File\Http;

class FileTransferFactory
{
    /**
     * Create HTTP adapter
     *
     * @param array $options
     * @return Http
     */
    public function create(array $options = [])
    {
        return new Http($options);
    }
}
