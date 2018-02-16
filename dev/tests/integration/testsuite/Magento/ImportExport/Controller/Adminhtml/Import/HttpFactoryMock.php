<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml\Import;

use Magento\Framework\HTTP\Adapter\FileTransferFactory;

class HttpFactoryMock extends FileTransferFactory
{
    public function create(array $options = [])
    {
        return new \Magento\Framework\Validator\NotEmpty($options);
    }
}
