<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\FileSystem;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * CSV file operations wrapper.
 */
class Csv implements \Magento\Framework\Setup\Declaration\Schema\DataSavior\DumpAccessorInterface
{
    /**
     * Folder where will be persisted all csv dumps
     */
    const DUMP_FOLDER = 'declarative_dumps_csv';

    /**
     * @var int
     */
    private $baseBatchSize;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $fileDriver;

    /**
     * Csv constructor.
     * @param DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param int $baseBatchSize
     */
    public function __construct(
        DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        $baseBatchSize = 15000
    ) {
        $this->baseBatchSize = $baseBatchSize;
        $this->directoryList = $directoryList;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Save to csv data with batches.
     *
     * @param string $file
     * @param array $data
     * @return $this
     */
    public function save($file, array $data)
    {
        $file = $this->prepareFile($file);
        if (!count($data) || !$file) {
            return $this;
        }

        if (!file_exists($file)) {
            array_unshift($data, array_keys($data[0]));
        }

        $fh = fopen($file, 'a');

        foreach ($data as $dataRow) {
            fputcsv($fh, $dataRow,',','"','\\');
        }

        fclose($fh);
        return $this;
    }

    /**
     * Prepare CSV file name
     *
     * @param string $file
     * @return string | bool
     */
    private function prepareFile($file)
    {
        $absolutePath = $this->directoryList->getPath(DirectoryList::VAR_DIR);

        if (!$this->fileDriver->isWritable($absolutePath)) {
            return false;
        }
        $dumpsPath = $absolutePath . DIRECTORY_SEPARATOR . self::DUMP_FOLDER;
        $this->ensureDirExists($dumpsPath);
        $filePath = $dumpsPath . DIRECTORY_SEPARATOR . $file . '.csv';
        return $filePath;
    }

    /**
     * Create directory if not exists
     *
     * @param string $dir
     */
    private function ensureDirExists($dir)
    {
        if (!$this->fileDriver->isExists($dir)) {
            $this->fileDriver->createDirectory($dir);
        }
    }

    /**
     * File read generator.
     *
     * This generator allows to load to memory only batch, with which we need to work at the moment
     *
     * @param string $file
     * @return \Generator
     */
    public function read($file)
    {
        $file = $this->prepareFile($file);

        if (!$this->fileDriver->isReadable($file)) {
            return [];
        }
        $data = [];
        $iterator = 0;
        $fh = fopen($file, 'r');
        $headers = fgetcsv($fh, null, ',', '"', '\\');
        $rowData = fgetcsv($fh, null, ',', '"', '\\');

        while ($rowData) {
            if ($iterator++ > $this->baseBatchSize) {
                $iterator = 0;
                $finalData = $data;
                $data = [];
                yield $this->processCsvData($finalData, $headers);
            }

            $data[] = $rowData;
            $rowData = fgetcsv($fh, null, ',', '"', '\\');
        }

        fclose($fh);
        yield $this->processCsvData($data, $headers);
    }

    /**
     * @param array $csvData
     * @param array $headers
     * @return array
     */
    private function processCsvData(array $csvData, array $headers)
    {
        $result = [];

        foreach ($csvData as $rowIndex => $csvRow) {
            foreach ($csvRow as $index => $item) {
                $result[$rowIndex][$headers[$index]] = $item;
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function destruct($resource)
    {
        $file = $this->prepareFile($resource);

        if ($this->fileDriver->isExists($file)) {
            $this->fileDriver->deleteFile($file);
        }
    }
}
