<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\ImportExport\Model\Export\Adapter;

use Magento\Framework\App\Filesystem\DirectoryList;
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
    private $destination = 'destinationFile';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Csv
     */
    private $csv;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->csv = $this->objectManager->create(
            Csv::class,
            ['destination' => $this->destination]
        );
    }

    /**
     * Test to destruct export adapter
     */
    public function testDestruct(): void
    {
        $this->csv->destruct();

        /** Assert that the destination file was removed after destruct */
        $destinationPath = $this->getAbsoluteFilePath(DirectoryList::VAR_DIR, $this->destination);
        $this->assertFileNotExists($destinationPath, 'The destination file was\'t removed after destruct');
    }

    /**
     * Get absolute file path
     *
     * @param string $path
     * @param string $fileName
     * @return string
     */
    private function getAbsoluteFilePath(string $path, string $fileName): string
    {
        /** @var DirectoryList $directoryList */
        $directoryList = $this->objectManager->get(DirectoryList::class);

        return $directoryList->getPath($path) . '/' . $fileName;
    }
}
