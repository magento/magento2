<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\ImportExport\Model\Export\Adapter;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Import;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for Export adapter csv
 */
class CsvTest extends TestCase
{
    /**
     * @var string Destination file name
     */
    private static $destination = 'destinationFile';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Test to destruct export adapter
     *
     * @dataProvider destructDataProvider
     *
     * @param string $destination
     * @param bool $shouldBeDeleted
     * @return void
     */
    public function testDestruct(string $destination, bool $shouldBeDeleted): void
    {
        $csv = $this->objectManager->create(Csv::class, [
            'destination' => $destination,
            'destinationDirectoryCode' => DirectoryList::VAR_DIR
        ]);
        /** @var Filesystem $fileSystem */
        $fileSystem = $this->objectManager->get(Filesystem::class);
        $directoryHandle = $fileSystem->getDirectoryRead(DirectoryList::VAR_DIR);
        /** Assert that the destination file is present after construct */
        $this->assertFileExists(
            $directoryHandle->getAbsolutePath($destination),
            'The destination file was\'t created after construct'
        );
        unset($csv);

        if ($shouldBeDeleted) {
            $this->assertFileDoesNotExist($directoryHandle->getAbsolutePath($destination));
        } else {
            $this->assertFileExists($directoryHandle->getAbsolutePath($destination));
        }
    }

    /**
     * DataProvider for testDestruct
     *
     * @return array
     */
    public static function destructDataProvider(): array
    {
        return [
            'temporary file' => [self::$destination, true],
            'import history file' => [Import::IMPORT_HISTORY_DIR . self::$destination, false],
        ];
    }
}
