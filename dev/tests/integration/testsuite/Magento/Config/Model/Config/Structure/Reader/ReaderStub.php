<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Model\Config\Structure\Reader;

use Magento\Config\Model\Config\Structure\Reader;

/**
 * Class ReaderStub used for testing protected Reader::_readFiles() method.
 */
class ReaderStub extends Reader
{
    /**
     * Wrapper for protected Reader::_readFiles() method.
     *
     * @param array $fileList
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function readFiles(array $fileList)
    {
        return $this->_readFiles($fileList);
    }
}
