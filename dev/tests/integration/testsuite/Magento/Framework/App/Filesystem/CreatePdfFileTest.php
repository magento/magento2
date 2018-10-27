<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\Framework\App\Filesystem;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;
use Zend\Http\Header\ContentType;

/**
 * Class CreatePdfFileTest
 *
 * Integration test for testing a file creation from string
 */
class CreatePdfFileTest extends \PHPUnit\Framework\TestCase
{
    public function testGenerateFileFromString()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var FileFactory $fileFactory */
        $fileFactory =  $objectManager->get(FileFactory::class);
        /** @var Filesystem $filesystem */
        $filesystem = $objectManager->get(Filesystem::class);
        $filename = 'test.pdf';
        $contentType = 'application/pdf';
        $fileContent = ['type' => 'string', 'value' => ''];
        $response = $fileFactory->create($filename, $fileContent, DirectoryList::VAR_DIR, $contentType);
        /** @var ContentType $contentTypeHeader */
        $contentTypeHeader = $response->getHeader('Content-type');

        /* Check the system returns the correct type */
        self::assertEquals("Content-Type: $contentType", $contentTypeHeader->toString());

        $varDirectory = $filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
        $varDirectory->isFile($filename);

        /* Check the file is generated */
        self::assertTrue($varDirectory->isFile($filename));

        /* Check the file is removed after generation if the corresponding option is set */
        $fileContent = ['type' => 'string', 'value' => '', 'rm' => true];
        $fileFactory->create($filename, $fileContent, DirectoryList::VAR_DIR, $contentType);

        self::assertFalse($varDirectory->isFile($filename));
    }
}
