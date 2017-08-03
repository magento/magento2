<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\Adapter;

/**
 * Class \Magento\Framework\HTTP\Adapter\FileTransferFactory
 *
 * @since 2.0.0
 */
class FileTransferFactory
{
    /**
     * Create HTTP adapter
     *
     * @param array $options
     * @return \Zend_File_Transfer_Adapter_Http
     * @since 2.0.0
     */
    public function create(array $options = [])
    {
        return new \Zend_File_Transfer_Adapter_Http($options);
    }
}
