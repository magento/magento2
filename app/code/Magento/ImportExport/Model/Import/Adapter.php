<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Import;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\ImportExport\Model\Import\Source\FileFactory;
use Magento\ImportExport\Model\Import\Source\FileParser\CorruptedFileException;
use Magento\ImportExport\Model\Import\Source\FileParser\UnsupportedPathException;

/**
 * Import adapter model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Adapter
{
    /** @var FileFactory */
    private $fileSourceFactory;

    public function __construct(FileFactory $fileSourceFactory)
    {
        $this->fileSourceFactory = $fileSourceFactory;
    }


    /**
     * Adapter factory. Checks for availability, loads and create instance of import adapter object.
     *
     * @param string $type Adapter type ('csv', 'xml' etc.)
     * @param Write $directory
     * @param string $source
     * @param mixed $options OPTIONAL Adapter constructor options
     *
     * @return AbstractSource
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @deprecated
     * @see Adapter::createSourceByPath()
     */
    public static function factory($type, $directory, $source, $options = null)
    {
        return self::createBackwardCompatibleInstance()->createSourceByPath($source, $options);
    }

    /**
     * Create adapter instance for specified source file.
     *
     * @param string $source Source file path.
     * @param Write $directory
     * @param mixed $options OPTIONAL Adapter constructor options
     *
     * @return AbstractSource
     * @deprecated
     * @see Adapter::createSourceByPath()
     */
    public static function findAdapterFor($source, $directory, $options = null)
    {
        return self::createBackwardCompatibleInstance()->createSourceByPath($source, $options);
    }



    /**
     * Finds source for import by file path
     *
     * @param $path
     * @param $options
     *
     * @return AbstractSource
     */
    public function createSourceByPath($path, $options = [])
    {
        try {
            $options = $this->mapBackwardCompatibleFileParserOption($options);
            $parser = $this->fileSourceFactory->createFromFilePath($path, $options);
        } catch (UnsupportedPathException $e) {
            $this->throwUnsupportedFileException($path);
        } catch (CorruptedFileException $e) {
            $this->throwUnsupportedFileException($path);
        }

        return $parser;
    }


    private function extractFileExtension($path)
    {
        $fileName = basename($path);

        if (strpos($path, '.') === false) {
            return $fileName;
        }

        $fileExtension = substr($fileName, strrpos($fileName, '.') + 1);
        return $fileExtension;
    }

    private function throwUnsupportedFileException($path)
    {
        $fileExtension = $this->extractFileExtension($path);
        throw new \Magento\Framework\Exception\LocalizedException(
            __('\'%1\' file extension is not supported', $fileExtension)
        );
    }

    private function mapBackwardCompatibleFileParserOption($options)
    {
        if (!is_array($options)) {
            $options = ['delimiter' => $options ?? ','];
        }
        return $options;
    }

    /**
     * @return self
     */
    private static function createBackwardCompatibleInstance()
    {
        return ObjectManager::getInstance()->create(self::class);
    }
}
