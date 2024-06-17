<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Fixture;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class CsvFile implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'directory' => DirectoryList::TMP,
        'path' => 'import/%uniqid%.csv',
        'rows' => [],
    ];

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var ProcessorInterface
     */
    private ProcessorInterface $dataProcessor;

    /**
     * @var DataObjectFactory
     */
    private DataObjectFactory $dataObjectFactory;

    /**
     * @param Filesystem $filesystem
     * @param ProcessorInterface $dataProcessor
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        Filesystem $filesystem,
        ProcessorInterface $dataProcessor,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->filesystem = $filesystem;
        $this->dataProcessor = $dataProcessor;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as CsvFile::DEFAULT_DATA.
     * Additional fields:
     * - $data['rows']: CSV data to be written into the file in the following format:
     *      - headers are listed in the first array and the following array
     *      [
     *          ['col1', 'col2'],
     *          ['row1col1', 'row1col2'],
     *      ]
     *      - headers are listed as array keys
     *      [
     *          ['col1' => 'row1col1', 'col2' => 'row1col2'],
     *          ['col1' => 'row2col1', 'col2' => 'row2col2'],
     *      [
     *
     * @see CsvFile::DEFAULT_DATA
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = $this->dataProcessor->process($this, array_merge(self::DEFAULT_DATA, $data));
        $rows = $data['rows'];
        $row = reset($rows);

        if (array_is_list($row)) {
            $cols = $row;
            $colsCount = count($cols);
            foreach ($rows as $row) {
                if ($colsCount !== count($row)) {
                    throw new \InvalidArgumentException('Arrays in "rows" must be the same size');
                }
            }
        } else {
            $cols = array_keys($row);
            $lines[] = $cols;
            foreach ($rows as $row) {
                $line = [];
                if (array_diff($cols, array_keys($row))) {
                    throw new \InvalidArgumentException('Arrays in "rows" must have same keys');
                }
                foreach ($cols as $field) {
                    $line[] = $row[$field];
                }
                $lines[] = $line;
            }
            $rows = $lines;
        }
        $directory = $this->filesystem->getDirectoryWrite($data['directory']);
        $file = $directory->openFile($data['path'], 'w+');
        foreach ($rows as $row) {
            $file->writeCsv($row);
        }
        $file->close();
        $data['absolute_path'] = $directory->getAbsolutePath($data['path']);

        return $this->dataObjectFactory->create(['data' => $data]);
    }

    /**
     * @inheritDoc
     */
    public function revert(DataObject $data): void
    {
        $directory = $this->filesystem->getDirectoryWrite($data['directory']);
        $directory->delete($data['path']);
    }
}
