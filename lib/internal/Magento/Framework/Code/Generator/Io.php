<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Generator;

use Magento\Framework\Exception\FileSystemException;

/**
 * Manages generated code.
 * @since 2.0.0
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
     * @since 2.0.0
     */
    private $_generationDirectory;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     * @since 2.0.0
     */
    private $filesystemDriver;

    /**
     * @param \Magento\Framework\Filesystem\Driver\File $filesystemDriver
     * @param null|string $generationDirectory
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function makeGenerationDirectory()
    {
        return $this->_makeDirectory($this->_generationDirectory);
    }

    /**
     * @param string $className
     * @return bool
     * @since 2.0.0
     */
    public function makeResultFileDirectory($className)
    {
        return $this->_makeDirectory($this->getResultFileDirectory($className));
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getGenerationDirectory()
    {
        return $this->_generationDirectory;
    }

    /**
     * @param string $fileName
     * @return bool
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function includeFile($fileName)
    {
        return include $fileName;
    }

    /**
     * @param string $directory
     * @return bool
     * @since 2.0.0
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
