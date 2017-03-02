<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Config\Structure\Reader;

/**
 * Class ReaderStub
 */
class ReaderStub extends \Magento\Config\Model\Config\Structure\Reader
{
    /**
     * @param array $fileList
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function readFiles(array $fileList)
    {
        return $this->_readFiles($fileList);
    }
}
