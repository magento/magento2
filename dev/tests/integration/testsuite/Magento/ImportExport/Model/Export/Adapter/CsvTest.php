<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\ImportExport\Model\Export\Adapter;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
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
        /** @var Filesystem $fileSystem */
        $fileSystem = $this->objectManager->get(Filesystem::class);
        $directoryHandle = $fileSystem->getDirectoryRead(DirectoryList::VAR_DIR);
        $this->assertFalse(
            $directoryHandle->isExist($this->destination),
            'The destination file was\'t removed after destruct'
        );
    }
}
