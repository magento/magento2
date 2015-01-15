<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System;

class FileManager
{
    /**
     * @var \Magento\Tools\Migration\System\FileReader
     */
    protected $_reader;

    /**
     * @var \Magento\Tools\Migration\System\WriterInterface
     */
    protected $_writer;

    /**
     * @param \Magento\Tools\Migration\System\FileReader $reader
     * @param \Magento\Tools\Migration\System\WriterInterface $writer
     */
    public function __construct(
        \Magento\Tools\Migration\System\FileReader $reader,
        \Magento\Tools\Migration\System\WriterInterface $writer
    ) {
        $this->_reader = $reader;
        $this->_writer = $writer;
    }

    /**
     * @param string $fileName
     * @param string $contents
     * @return void
     */
    public function write($fileName, $contents)
    {
        $this->_writer->write($fileName, $contents);
    }

    /**
     * Remove file
     *
     * @param string $fileName
     * @return void
     */
    public function remove($fileName)
    {
        $this->_writer->remove($fileName);
    }

    /**
     * Retrieve contents of a file
     *
     * @param string $fileName
     * @return string
     */
    public function getContents($fileName)
    {
        return $this->_reader->getContents($fileName);
    }

    /**
     * Get file list
     *
     * @param string $pattern
     * @return string[]
     */
    public function getFileList($pattern)
    {
        return $this->_reader->getFileList($pattern);
    }
}
