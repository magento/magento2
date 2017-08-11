<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Import\Source\FileParser;


use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;

class CsvParserFactory implements ParserFactoryInterface
{
    /**
     * File system adapter
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Object Manager for CSV parser creation
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(Filesystem $filesystem, ObjectManagerInterface $objectManager)
    {
        $this->filesystem = $filesystem;
        $this->objectManager = $objectManager;
    }

    public function create($filePath, array $options = [])
    {
        $directoryCode = $options['directory_code'] ?? DirectoryList::ROOT;

        if (substr($filePath, -4) !== '.csv') {
            throw new UnsupportedPathException($filePath);
        }

        $directory = $this->filesystem->getDirectoryRead($directoryCode);
        $filePath = $directory->getRelativePath($filePath);

        if (!$directory->isFile($filePath)) {
            throw new \InvalidArgumentException(sprintf('File "%s" does not exists', $filePath));
        }

        return $this->objectManager->create(
            CsvParser::class,
            [
                'file' => $directory->openFile($filePath),
                'options' => $options
            ]
        );
    }
}
