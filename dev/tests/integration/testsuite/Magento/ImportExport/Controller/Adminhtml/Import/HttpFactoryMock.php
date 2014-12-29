<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\ImportExport\Controller\Adminhtml\Import;

use Magento\Framework\HTTP\Adapter\FileTransferFactory;

class HttpFactoryMock extends FileTransferFactory
{

    public function create(array $options = [])
    {
        return new \Magento\Framework\Validator\NotEmpty();
    }
}