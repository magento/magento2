<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Generator;

use Magento\Framework\Exception\FileSystemException;

/**
 * Manages generated code.
 */
class Io
{
    /**
     * Default code generation directory
     * Should correspond the value from \Magento\Framework\Filesystem
     */
    const DEFAULT_DIRECTORY = 'generated/code';

    /**
     * Path to directory where new file must be created
     *
     * @var string
     */
    private $_generationDirectory;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $filesystemDriver;

    /**
     * @param \Magento\Framework\Filesystem\Driver\File $filesystemDriver
     * @param null|string $generationDirectory
     */
    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $filesystemDriver,
        $generationDirectory = null
    ) {
        $this->filesystemDriver = $filesystemDriver;
        $this->initGeneratorDirectory($generationDirectory);
    }

    /**
     * Get path to generation directory
     *
     * @param null|string $directory
     * @return string
     */
    protected function initGeneratorDirectory($directory = null)
    {
        if ($directory) {
            $this->_generationDirectory = rtrim($directory, '/') . '/';
        } else {
            $this->_generationDirectory = realpath(__DIR__ . '/../../../../') . '/' . self::DEFAULT_DIRECTORY . '/';
        }
    }

    /**
     * @param string $className
     * @return string
     */
    public function getResultFileDirectory($className)
    {
        $fileName = $this->generateResultFileName($className);
        $pathParts = explode('/', $fileName);
        unset($pathParts[count($pathParts) - 1]);

        return implode('/', $pathParts) . '/';
    }

    /**
     * @param string $className
     * @return string
     */
    public function generateResultFileName($className)
    {
        return $this->_generationDirectory . ltrim(str_replace(['\\', '_'], '/', $className), '/') . '.php';
    }

    /**
     * @param string $fileName
     * @param string $content
     * @throws FileSystemException
     * @return bool
     */
    public function writeResultFile($fileName, $content)
    {
        /**
         * Rename is atomic on *nix systems, while file_put_contents is not. Writing to a
         * temporary file whose name is process-unique and renaming to the real location helps
         * avoid race conditions. Race condition can occur if the compiler has not been run, when
         * multiple processes are attempting to access the generated file simultaneously.
         */
        $content = "<?php\n" . $content;
        $tmpFile = $fileName . "." . getmypid();
        $this->filesystemDriver->filePutContents($tmpFile, $content);

        try {
            $success = $this->filesystemDriver->rename($tmpFile, $fileName);
        } catch (FileSystemException $e) {
            if (!$this->fileExists($fileName)) {
                throw $e;
            } else {
                /**
                 * Due to race conditions, file may have already been written, causing rename to fail. As long as
                 * the file exists, everything is okay.
                 */
                $success = true;
            }
        }

        return $success;
    }

    /**
     * @return bool
     */
    public function makeGenerationDirectory()
    {
        return $this->_makeDirectory($this->_generationDirectory);
    }

    /**
     * @param string $className
     * @return bool
     */
    public function makeResultFileDirectory($className)
    {
        return $this->_makeDirectory($this->getResultFileDirectory($className));
    }

    /**
     * @return string
     */
    public function getGenerationDirectory()
    {
        return $this->_generationDirectory;
    }

    /**
     * @param string $fileName
     * @return bool
     */
    public function fileExists($fileName)
    {
        return $this->filesystemDriver->isExists($fileName);
    }

    /**
     * Wrapper for include
     *
     * @param string $fileName
     * @return mixed
     * @codeCoverageIgnore
     */
    public function includeFile($fileName)
    {
        return include $fileName;
    }

    /**
     * @param string $directory
     * @return bool
     */
    private function _makeDirectory($directory)
    {
        if ($this->filesystemDriver->isWritable($directory)) {
            return true;
        }
        try {
            if (!$this->filesystemDriver->isDirectory($directory)) {
                $this->filesystemDriver->createDirectory($directory);
            }
            return true;
        } catch (FileSystemException $e) {
            return false;
        }
    }
}
