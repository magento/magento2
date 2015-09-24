<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Model;

use Magento\Framework\Setup;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class State implements Setup\SampleData\StateInterface
{
    const ERROR = 'error';

    /**
     * @var string
     */
    protected $fileName = 'sample-data-state.flag';

    /**
     * @var string|null
     */
    protected $filePath;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @inheritdoc
     */
    public function hasError()
    {
        $isError = false;
        $stream = $this->getStream('r', $this->fileName);
        if (!$stream) {
            return $isError;
        } elseif (trim(fread($stream, 400)) == self::ERROR) {
            $isError = true;
        }
        $this->closeStream($stream);
        return $isError;
    }

    /**
     * @inheritdoc
     */
    public function setError()
    {
        if (!$this->hasError()) {
            $this->writeStream(self::ERROR, $this->fileName);
        }
    }

    /**
     * Clear Sample Data state
     *
     * @return void
     *
     */
    public function clearState()
    {
        $this->writeStream('', $this->fileName);
    }

    /**
     * @return null|string
     */
    protected function getFilePath()
    {
        if (!isset($this->filePath)) {
            $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->getAbsolutePath();
            $this->filePath = $directory . $this->fileName;
        }
        return $this->filePath;
    }

    /**
     * Get file resource to write sample data installation state
     *
     * @param string $mode
     * @return resource|false
     */
    protected function getStream($mode = 'r')
    {
        $stream = @fopen($this->getFilePath(), $mode);
        return $stream;
    }

    /**
     * @param string $data
     * @throws \Exception
     */
    protected function writeStream($data)
    {
        $stream = $this->getStream('w');
        if ($stream === false) {
            throw new \Exception(
                'Please, ensure that file var/' . $this->getFilePath()
                    . ' inside Sample data directory exists and is writable'
            );
        }
        fwrite($stream, $data);
        $this->closeStream($stream);
    }

    /**
     * Closing file stream
     *
     * @param resource|false $handle
     * @return void
     */
    protected function closeStream($handle)
    {
        if ($handle) {
            fclose($handle);
        }
    }
}
