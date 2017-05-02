<?php

namespace Magento\ImportExport\Model\Import\Source\FileParser;


use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class CsvParserFactory implements ParserFactoryInterface
{
    /**
     * File system adapter
     *
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function create($filePath, array $options = [])
    {
        $directoryCode = $options['directory_code'] ?? DirectoryList::ROOT;

        if (substr($filePath,-4) !== '.csv') {
            throw new UnsupportedPathException($filePath);
        }

        $directory = $this->filesystem->getDirectoryRead($directoryCode);

        if (!$directory->isFile($filePath)) {
            throw new \InvalidArgumentException(sprintf('File "%s" does not exists', $filePath));
        }

        return new CsvParser($directory->openFile($filePath));
    }
}
