<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Import\Source\FileParser;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class ZipParserFactory implements ParserFactoryInterface
{
    private $filesystem;
    private $isZipAvailable;
    private $parserFactory;

    public function __construct(
        Filesystem $filesystem,
        ParserFactoryInterface $parserFactory,
        $isZipAvailable = null
    ) {
        $this->filesystem = $filesystem;
        $this->parserFactory = $parserFactory;
        $this->isZipAvailable = $isZipAvailable ?? extension_loaded('zip');
    }

    public function create($path, array $options = [])
    {
        $this->assertZipFileIsParsable($path);

        $sourceDirectory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        $writeDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);

        $path = $sourceDirectory->getRelativePath($path);
        $this->assertFileExistance($path, $sourceDirectory);

        $zip = new \ZipArchive();

        if ($zip->open($sourceDirectory->getAbsolutePath($path)) !== true) {
            throw new CorruptedFileException($path);
        }

        $files = $this->fetchFileNamesFromZipFile($zip);

        foreach ($files as $file) {
            $destinationFilename = $this->extractFileIntoDirectory($zip, $writeDirectory, $file);

            try {
                $parser = $this->parserFactory->create(
                    $destinationFilename,
                    ['directory_code' => DirectoryList::TMP] + $options
                );
            } catch (UnsupportedPathException $e) {
                continue;
            } finally {
                $writeDirectory->delete($destinationFilename);
            }

            return $parser;
        }

        throw new UnsupportedPathException($path);
    }

    private function assertZipFileIsParsable($path)
    {
        if (substr($path, -4) !== '.zip') {
            throw new UnsupportedPathException($path);
        }

        if (!$this->isZipAvailable) {
            throw new UnsupportedPathException($path, 'Zip extension is not available');
        }
    }

    private function assertFileExistance($path, $directory)
    {
        if (!$directory->isFile($path)) {
            throw new UnsupportedPathException($path);
        }
    }

    private function fetchFileNamesFromZipFile(\ZipArchive $zip)
    {
        $files = [];

        for ($fileIndex = 0; $fileIndex < $zip->numFiles; $fileIndex++) {
            $fileName = $zip->getNameIndex($fileIndex);
            if ($this->isDirectoryZipEntry($fileName) || !$fileName) {
                continue;
            }

            $files[] = $fileName;
        }

        return $files;
    }

    private function isDirectoryZipEntry($fileName)
    {
        return substr($fileName, -1) === '/';
    }

    private function extractFileIntoDirectory(
        \ZipArchive $zip,
        Filesystem\Directory\WriteInterface $writeDirectory,
        $fileName
    ) {
        $destinationFilename = 'tmp-' . uniqid() . '-' . basename($fileName);

        $destinationFileWriter = $writeDirectory->openFile($destinationFilename);
        $destinationFileWriter->write($zip->getFromName($fileName));
        $destinationFileWriter->close();

        return $destinationFilename;
    }
}
