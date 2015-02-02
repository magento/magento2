<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Generator;

class Io
{
    /**
     * Default code generation directory
     * Should correspond the value from \Magento\Framework\Filesystem
     */
    const DEFAULT_DIRECTORY = 'var/generation';

    /**
     * \Directory permission for created directories
     */
    const DIRECTORY_PERMISSION = 0777;

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
        $fileName = $this->getResultFileName($className);
        $pathParts = explode('/', $fileName);
        unset($pathParts[count($pathParts) - 1]);

        return implode('/', $pathParts) . '/';
    }

    /**
     * @param string $className
     * @return string
     */
    public function getResultFileName($className)
    {
        return $this->_generationDirectory . ltrim(str_replace(['\\', '_'], '/', $className), '/') . '.php';
    }

    /**
     * @param string $fileName
     * @param string $content
     * @return bool
     */
    public function writeResultFile($fileName, $content)
    {
        $content = "<?php\n" . $content;
        return $this->filesystemDriver->filePutContents($fileName, $content);
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
                $this->filesystemDriver->createDirectory($directory, self::DIRECTORY_PERMISSION);
            }
            return true;
        } catch (\Magento\Framework\Filesystem\FilesystemException $e) {
            return false;
        }
    }
}
