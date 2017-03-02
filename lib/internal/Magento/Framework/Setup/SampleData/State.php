<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\SampleData;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class State implements StateInterface
{
    /**
     * @var string
     */
    protected $fileName = '.sample-data-state.flag';

    /**
     * @var string|null
     */
    protected $filePath;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Filesystem $filesystem
     */
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
        $stream = $this->openStream('r');
        if (!$stream) {
            return $isError;
        } elseif (strpos(trim($stream->read(400)), self::ERROR) !== false) {
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
            $this->writeStream(self::ERROR);
        }
    }

    /**
     * @inheritdoc
     */
    public function isInstalled()
    {
        $isInstalled = false;
        /**@var $stream \Magento\Framework\Filesystem\File\WriteInterface */
        $stream = $this->openStream('r');
        if (!$stream) {
            return $isInstalled;
        } else {
            $state = trim($stream->read(400));
            if (strpos($state, self::ERROR) !== false || strpos($state, self::INSTALLED) !== false) {
                $isInstalled = true;
            }
        }
        $this->closeStream($stream);
        return $isInstalled;
    }

    /**
     * @inheritdoc
     */
    public function setInstalled()
    {
        if (!$this->isInstalled()) {
            $this->writeStream(self::INSTALLED);
        }
    }

    /**
     * @inheritdoc
     */
    public function clearState()
    {
        if ($this->openStream('w')) {
            $this->writeStream('');
        }
    }

    /**
     * @return \Magento\Framework\Filesystem\File\WriteInterface
     */
    protected function getStream()
    {
        if (!$stream = $this->openStream('w')) {
            $stream = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->openFile($this->fileName);
        }
        return $stream;
    }

    /**
     * @param string $mode
     * @return bool|\Magento\Framework\Filesystem\File\WriteInterface
     */
    protected function openStream($mode = 'w')
    {
        $fileName = $this->fileName;
        $stream = false;
        $directoryWrite = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        if ($directoryWrite->isExist($fileName)) {
            $stream = $directoryWrite->openFile($fileName, $mode);
        }
        return $stream;

    }

    /**
     * @param string $data
     * @throws \Exception
     * @return void
     */
    protected function writeStream($data)
    {
        $stream = $this->getStream();
        if ($stream === false) {
            throw new \Exception(
                'Please ensure that the ' . $this->fileName
                . ' file exists in the var directory and is writable.'
            );
        }
        $stream->write($data);
        $this->closeStream($stream);
    }

    /**
     * Closing file stream
     *
     * @param \Magento\Framework\Filesystem\File\WriteInterface $stream
     * @return void
     */
    protected function closeStream($stream)
    {
        if ($stream) {
            $stream->close();
        }
    }
}
